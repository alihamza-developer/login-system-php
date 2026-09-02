const fancyCheckbox = () => {
    $(".fancy-checkbox").each(function () {
        if (this.hasAttribute("data-fetched")) return;
        $(this).attr("data-fetched", "true");
        let checkbox = $(this).clone(),
            type = $(this).inputType(),
            label = "",
            extraClass = $(this).dataVal("extra-class", "");

        if (this.hasAttribute('data-label')) label = $(this).attr("data-label");
        if ($(this).hasAttr("data-extra-class") === "")
            checkbox.removeClass("fancy-checkbox");
        checkbox.addClass("nc-code-lable");
        checkbox.addClass("checkbox");
        let html = `
                    <label class="checkboxLabel ${extraClass} ${type}">
                        ${checkbox.get(0).outerHTML}
                        <span class="c-box">
                            ${SVG_ICONS.check}
                        </span>
                        <span>${label}</span>
                    </label>
                `;
        $(html).insertBefore($(this));
        $(this).remove();
    });
}

// #region JX Req Element
// Ajax Request Elements
function initJxReqElements(selector) {
    $(selector).each(function () {
        if (!$(this).hasAttr("data-launched")) {
            let event = $(this).dataVal("listener"),
                tagName = $(this).tagName();
            if (!event) {
                if (['input', 'select', 'textarea'].includes(tagName))
                    event = "change";
                else if ($(this).hasAttr("contenteditable"))
                    event = "focusout";
                else
                    event = "click";
            }
            $(this).attr("data-launched", "true");
            $(this).on(event, function () {
                // Setting element (if settings element (for data target,submit,include, etc) is another)
                let $settingsEl = $(this);
                if ($(this).dataVal("settings")) {
                    $settingsEl = $($(this).data("settings")).first();
                    let radius = $(this).dataVal("radius");
                    if (radius) $settingsEl = $(this).parents(radius).find($(this).data("settings"));
                    ['return-callback'].forEach(attr => {
                        let attrValue = $settingsEl.dataVal(attr);
                        if (attrValue) {
                            $(this).attr(`data-${attr}`, attrValue);
                        }
                    });
                }
                // Select Attributes data
                let targetUrl = $(this).dataVal("target") ? $(this).dataVal("target") : $settingsEl.dataVal("target"),
                    submitData = $(this).dataVal("submit") ? $(this).dataVal("submit") : $settingsEl.dataVal("submit", {}),
                    dataIncludeSel = $(this).dataVal("include") ? $(this).dataVal("include") : $settingsEl.dataVal("include"),
                    showPercentage = $(this).dataVal("show-percentage"),
                    dataInclude = {},
                    name = $(this).dataVal("name"),
                    type = $(this).inputType(),
                    elValue = null,
                    showAlert = true;

                if ($settingsEl.hasAttr("data-show-alert")) {
                    if ($settingsEl.data("show-alert") == false) {
                        showAlert = false;
                    }
                }

                if (typeof submitData === "string") {
                    if (!isJson(submitData)) return logError("data-submit is not json");
                    submitData = JSON.parse(submitData);
                }

                if (!name) name = $(this).attr("name");
                if (!targetUrl) return logError("data-target attribute not found");
                // Append value
                if (event === "focusout")
                    elValue = $(this).text();
                if (event === "change") {
                    if (['radio', 'checkbox'].includes(type)) {
                        let checkedEl = $(this);
                        elValue = checkedEl.is(":checked");
                        if (checkedEl.hasAttr("value"))
                            submitData[`${name}Value`] = checkedEl.val();
                    } else if (['file'].includes(type)) {
                        let fileInput = $(this).get(0),
                            files = fileInput.files;
                        elValue = files;
                    } else
                        elValue = $(this).val();
                }
                if (elValue !== null) submitData[name] = elValue;
                // Data include
                if (dataIncludeSel) {
                    let radius = $settingsEl.dataVal("radius"),
                        $parent = $("body").first();
                    if (radius) {
                        $parent = $(this).parents(radius);
                    }
                    dataInclude = $parent.find(dataIncludeSel).serializeArray();
                }
                // Mege Data
                for (let key in dataInclude) {
                    let data = dataInclude[key];
                    if (data.name.length)
                        submitData[data.name] = data.value;
                }
                // Show loader
                let loader = $(this).dataVal("loader");
                if (!loader) {
                    loader = (event === "click") ? true : false;
                } else {
                    loader == "false" ? false : true;
                }
                let requestData = {
                    data: submitData,
                    url: targetUrl,
                    element: $(this),
                    showAlert,
                    loader,
                    type
                };
                // Has show percentage
                if (showPercentage) requestData.percentageTarget = showPercentage;

                JxRequest(requestData);
            });
        }
    });
}
// Ajax Request Fn
function JxRequestSend(request) {
    let { url, data, element, loader, showAlert, percentageTarget, type } = request,
        $elem = $(element),
        elementHtml = $elem.html(),
        jsonData = $elem.dataVal("res-type", true),
        formData = new FormData(),
        isFile = false;

    jsonData = toBoolean(jsonData);
    if (url.indexOf("./") === -1) url = `./controllers/${url}`;
    // Check is file
    if (type == 'file') {
        for (const key in data) {
            let item = data[key];
            if (item instanceof FileList) {
                for (let i = 0; i < item.length; i++) {
                    let file = item[i];
                    formData.append(key, file);
                }
                continue;
            }
            formData.append(key, item);
        }
        isFile = true;
    }

    // Callback
    let callback = $elem.dataVal("callback");
    let ajaxData = {
        url: url,
        type: "POST",
        data: isFile ? formData : data,
        beforeSend: function () {
            if (loader) disableBtn(element);
        },
        success: function (response) {
            if (isFile) element.val('');

            if (isFile && percentageTarget) $(percentageTarget).dnone(true);



            if (loader) enableBtn(element, elementHtml);
            if (callback) return fn._handle(element, response);
            if (!showAlert) return true;
            if (!isJson(response)) return false;

            response = JSON.parse(response);

            // Download Any File
            if ("download" in response) {
                let { filename, url } = response.download;
                downloadFile(filename, url);
                return false;
            }

            // Alert
            if ("redirect" in response) {

                if (response.redirect === "refresh")
                    location.reload();
                else
                    location.assign(response.redirect);

            } else
                sAlert(response.data, response.status);

        },
        error: function () {
            if (loader)
                enableBtn(element, elementHtml);
            makeError();
        }
    };


    // Show percentage of file upload
    if (isFile && percentageTarget) {
        let $percentageTarget = $(percentageTarget);
        if (!$percentageTarget.length) return logError("Percentage target not found");
        $percentageTarget.text("0%");
        // Show The target element if it is hidden
        if ($percentageTarget.hasClass('d-none')) $percentageTarget.removeClass('d-none');
        // Set ajax xhr
        ajaxData.xhr = function () {
            let xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    // Calculate percentage
                    let percentComplete = evt.loaded / evt.total;
                    percentComplete = parseInt(percentComplete * 100);
                    // Show percentage
                    $percentageTarget.text(percentComplete + "%");
                    if (percentComplete === 100) $percentageTarget.text("Processing...");
                }
            }, false);
            return xhr;
        };
    }

    // Check if contain file then set file options
    if (isFile) {
        ajaxData.processData = false;
        ajaxData.contentType = false;
    }


    $.ajax(ajaxData);
}
// Send Ajax request
function JxRequest(request) {
    let { element, loader, showAlert } = request,
        $elem = $(element);
    // Loader
    if (!("loader" in request)) {
        if ($elem.dataVal("loader", null) !== null) {
            loader = $elem.data("loader");
        }
    }
    loader = JSON.parse(loader);
    request.loader = loader;
    // Show Alert
    if (!("showAlert" in request)) {
        if ($elem.dataVal("alert", null) !== null) {
            showAlert = $elem.data("alert");
        }
    }
    showAlert = JSON.parse(showAlert);
    request.showAlert = showAlert;
    // Confirm Data
    let dataConfirm = false;
    if ($elem.hasAttr("data-confirm")) {
        let isConfirm = $elem.data("confirm");
        if (isJson(isConfirm)) {
            dataConfirm = JSON.parse(isConfirm);
        } else {
            dataConfirm = isConfirm;
        }
    }
    if (dataConfirm) {
        Confirm.ask().then(result => {
            if (result.value) JxRequestSend(request);
        });
        return false;
    }
    JxRequestSend(request);

}
// #endregion JX Element
// #region Folding Card
//Collapse bar
$(document).on("click", ".folding-card .card-header", function (e) {
    if ($(e.target).data("prevent-slide") || $(e.target).parents("[data-prevent-slide]").length > 0) return true;
    let $cardBody = $(this).parents(".folding-card").first().find(".card-body").first();
    if ($(this).hasClass("active")) {
        $cardBody.slideUp("1000");
        $(this).removeClass("active");
    }
    else {
        $cardBody.slideDown("1000");
        $(this).addClass("active");
    }
});
// #endregion Folding Card

// #region Add Multiple HTML
$(document).on("click", '[data-toggle="addHTML"]', function () {
    let variables = $(this).dataVal('variables');
    if (variables) variables = JSON.parse(variables);
    else variables = {};
    dataPickAndDrop($(this), variables);
});
// #endregion Add HTML
// Bootstrap modal callback
$(document).on("show.bs.modal", ".modal[data-callback]", function (e) {
    fn._handle(this, e, 'callback');
});

$(document).ready(function () {
    $('input.focused').focus();
});

// Remove Parent
$(document).on("click", '.removeParent', function () {
    if (!$(this).hasAttr("data-target")) return logError("data-target attribute not found!");
    fn._handle(this);
    $(this).parents($(this).data("target")).first().remove();
});

// Get pick and drop elements
function getPickAndDropElements($elem, showError = false) {
    $elem = $($elem);
    if (!$elem.hasAttr("data-pick")) {
        if (!showError) return false;
        return logError("data-pick attribute not found!");
    }
    // Pick element
    let pickSelector = $elem.data("pick"),
        radius = $elem.dataVal("pick-radius", 'body'),
        $pick = $elem.parents(radius).first().find(pickSelector);

    if (!$pick.length) {
        if (!showError) return false;
        return logError("picking element not found!");
    }

    // drop element
    let dropSelector = $elem.data("drop");
    radius = $elem.dataVal("drop-radius", 'body');
    let $drop = $elem.parents(radius).first().find(dropSelector);

    if ($drop.length < 1) {
        if (!showError) return false;
        return logError("droping element not found!");
    }

    return {
        $pick,
        $drop
    }
}
// Pick data and drop in target
let addHTMLCount = 0;
function dataPickAndDrop($elem, variables = {}, options = {}) {
    let { addType = 'prepend', countChilds = false } = options;
    let elements = getPickAndDropElements($elem, true);
    if (!elements) return false;
    let { $pick, $drop } = elements;

    if (countChilds) {
        let count = $drop.children().length;
        if (addType == 'append')
            variables.count = count;
        else if (addType == 'prepend')
            refreshTableCount($drop);
    } else
        variables.count += ++addHTMLCount;

    // Append Data
    let html = $pick.clone().removeClass("d-none").removeAttr("id").prop('outerHTML');
    let res = fn._handle($elem, { html, variables }, 'before-addHTML'); // must return html
    if (typeof res === "object") {
        if (res.html) html = res.html;
        if (res.variables) variables = res.variables;
    }
    html = replaceVariables(html, variables);
    $drop[addType](html);
    // Get append element
    $drop.children().last().trigger(addType);
    fn._handle($elem, null, 'addHTML-callback');
}
// Refresh Table Count
function refreshTableCount($table) {
    let count = 1;
    $table.find("tr").each(function (index) {
        $(this).find("td:first-child").first().text(count++);
    });
}

// #region Preview image file from file input
$(document).on("change", ".file-preview-input", function () {
    let target = $(this).dataVal("target");
    if (!target) return logError("target not found!");
    let $target = $(target);
    if (!$target.length) return logError("target not found!");
    if (!this.files.length) return logError("file not found!");
    let file = this.files[0];

    if (!isImageFile(file)) return logError("file is not an image!");

    let reader = new FileReader();
    reader.onload = function (e) {
        $target.attr("src", e.target.result);
    };
    reader.readAsDataURL(file);
});
// #endregion Preview image file from file input

//#region Js Dropdown
function initJSDropdown() {
    $(".dropdown.js-dropdown:not([data-launched])").each(function () {
        let $dropdown = $(this),
            $menu = $dropdown.find('.dropdown-menu'),
            $toggle = $dropdown.find("[data-toggle='dropdown']"),
            $item = $menu.find('.dropdown-item'),
            $contentArea = $toggle.find('.content-area'),
            contentType = $dropdown.dataVal("content-type", 'html');
        $dropdown.attr("data-launched", true);

        // On click on Item
        $item.on("click", function () {
            let $this = $(this),
                value = $this.attr("value"),
                content = $this[contentType]();

            $dropdown.attr("data-value", value);
            if ($contentArea.length) $toggle = $contentArea;
            $toggle[contentType](content);
            $dropdown.trigger("change");
            $item.removeAttr("selected");
            $this.attr("selected", true);
        });

        // Load Default Value
        let $selected = $menu.find('.dropdown-item[selected]');
        $selected.trigger("click");
    });
}
//#endregion Js Dropdown 

//#region Sync Inputs
$(document).find(".sync-input").each(function () {
    let target = $(this).dataVal('to'),
        radius = $(this).dataVal("radius"),
        listener = $(this).dataVal("listener", 'input');
    if (!target) return true;

    let $target = $(target);
    if (radius) $target = $(radius).find(target);
    if (!$target.length) return false;

    // Sync to target
    $(this).on(listener, function () {
        let value = $(this).val(),
            targetListener = $target.dataVal("listener", 'input');
        $target.val(value);
        // $target.trigger(targetListener);
    });
    // Sync to from 
    $target.on($target.dataVal("listener", 'change'), function () {
        let value = $(this).val(),
            $from = $($(this).dataVal("from"));
        if (!$from.length) return false;
        $from.val(value);
        $from.trigger(listener)
    });
});
//#endregion Sync Inputs 