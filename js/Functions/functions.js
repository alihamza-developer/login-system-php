let l = console.log.bind(this),
    logError = console.error.bind(this);
function isFloat(n) {
    return Number(n) === n && n % 1 !== 0;
}
function getRand(len) {
    return createKey(len);
}
// Craete random key
function createKey(len) {
    let chars = "adeh9i8jklw6xo4bcmnp5q2rs3tu1fgv7yz0ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        key = "";
    for (let i = 0; i < len; i++) {
        key += chars[Math.floor(Math.random() * ((chars.length - 1) + 1))];
    }
    return key;
}
// is json
function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}    // Get Number
function toNumber(str) {
    if (typeof (str) == "number" || typeof (str) == "float") return str;
    if (str) {
        str = str.replace(/[^\d.]/g, "");
        if (str.length > 0) {
            str = parseFloat(str);
        }
    }
    str = parseFloat(str);
    if (isNaN(str)) {
        return false;
    } else {
        return str;
    }
}
// To boolean
function toBoolean(data) {
    if (typeof data === "boolean") return data;
    if (isJson(data)) {
        data = JSON.parse(data);
    }

    return data ? true : false;
}
// Alert Fuction
function sAlert(text, heading, options = {}) {
    let type = ("type" in options) ? options.type : false,
        html = ("html" in options) ? options.html : false;

    if (!type)
        type = heading.toLowerCase();

    let types = ["success", "error", "warning", "info"];
    if (!types.includes(type)) type = 'info';

    // a heading that only repeats the status adds nothing
    let title = heading || '';
    if (String(title).toLowerCase() === type) title = '';

    return Notify.show(text, type, { title: title, html: html });
}
// Handle Alert
function handleAlert(res, showSuccessAlert = true) {
    let success = false;
    if (typeof res === "string") {
        if (!isJson(res)) return false;
        else res = JSON.parse(res);
    }
    if (res.status == "success") success = true;
    let heading = ("heading" in res) ? res.heading : res.status;
    if (!success || showSuccessAlert)
        sAlert(res.data, heading, {
            type: res.status,
            ...res
        });
    return success;
}
// Error
function makeError(error = 'Something went wrong! Please try again') {
    if (typeof error !== "string")
        error = 'Something went wrong! Please try again';
    return Notify.error(error);
}
// Disaled button
function disableBtn(btn) {
    btn = $(btn);
    if (btn.data('btn-html') === undefined) btn.data('btn-html', btn.html());
    // keep the label so the button keeps its size
    btn.html('<span class="btn-label">' + btn.data('btn-html') + '</span>');
    btn.addClass('is-loading disabled');
    btn.prop('disabled', true);
}
// Enable button
function enableBtn(btn, text) {
    btn = $(btn);
    let original = btn.data('btn-html');
    btn.html(text !== undefined && text !== null ? text : original);
    btn.removeData('btn-html');
    btn.removeClass('is-loading disabled');
    btn.prop('disabled', false);
}
function isObject(obj) {
    if (obj.__proto__.toString) {
        return (obj.toString() == "[object Object]")
    }
    return false;
}
// Loader
function getLoader(text) {
    let loader = '';
    loader += '<div class="loader">';
    loader += '<span class="load"></span>';
    if (text)
        loader += '<span class="text">Loading</span>';
    loader += '</div>';
    return loader;
}
const loader = getLoader(false);
function convertObjToQueryStr(Obj) {
    return Object.keys(Obj).map(function (key) {
        return encodeURIComponent(key) + '=' +
            encodeURIComponent(Obj[key]);
    }).join('&');
}
// Scroll to element
function scrollTo_($elem, duration = 1000, direction = 'top', scrollFrom = 'html, body') {
    if (!$elem.length) return false;
    let scrollDir = "scroll" + capitalize(direction, true),
        offset = $elem.offset(),
        totalOffset = (direction in offset) ? offset[direction] : 0;

    let data = {};
    data[scrollDir] = totalOffset;
    $(scrollFrom).animate(totalOffset, duration);
}
// check if image file
function isImageFile(file) {
    let allowedExt = ['jpg', 'png', 'jpeg', 'gif', 'jfif'];
    let ext = file.name.split('.').pop().toLowerCase();
    if (allowedExt.includes(ext)) {
        return true;
    } else {
        return false;
    }
}

function mergePath(...paths) {
    let url = '';
    paths.forEach(path => {
        path = trim(path);
        path = trim(path, '/');
        if (path.length) url += `/${path}`;
    });
    url = trim(url, '/');
    return url;
}

function trim(str, charlist) {
    let whitespace = [' ', '\n', '\r', '\t', '\f', '\x0b', '\xa0', '\u2000', '\u2001', '\u2002', '\u2003', '\u2004', '\u2005', '\u2006', '\u2007', '\u2008', '\u2009', '\u200a', '\u200b', '\u2028', '\u2029', '\u3000'].join('')
    let l = 0
    let i = 0
    str += ''
    if (charlist) {
        whitespace = (charlist + '').replace(/([[\]().?/*{}+$^:])/g, '$1')
    }
    l = str.length
    for (i = 0; i < l; i++) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(i)
            break
        }
    }
    l = str.length
    for (i = l - 1; i >= 0; i--) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(0, i + 1)
            break
        }
    }
    return whitespace.indexOf(str.charAt(0)) === -1 ? str : ''
}

// Helper function to format file size in a human-readable way
function formatFileSize(size) {
    if (!size) return true;
    var units = ['B', 'KB', 'MB', 'GB', 'TB'];
    var i = 0;
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024;
        i++;
    }
    return size.toFixed(1) + ' ' + units[i];
}

// Helper function to get file icon class based on file type
function getFileIcon(fileType) {
    if (!fileType) return false;
    let icon = "";

    if (fileType.startsWith('image/')) icon = 'far fa-image';
    else if (fileType.startsWith('audio/')) icon = 'far fa-file-audio';
    else if (fileType.startsWith('video/')) icon = 'far fa-file-video';
    else if (fileType.endsWith('pdf')) icon = 'far fa-file-pdf';
    else icon = 'far fa-file';

    return icon;
}
// Refresh Functions
function refreshFns() {
    bsTooltips(); // bootstrap tooltips & Popover
    fancyCheckbox(); // Checkbox
    initTinymce(); // Tinymce
    if (typeof DataTable === "function") DataTable(); // DataTable
    initJxReqElements('.jx-req-element'); // Jx Elements
}
$(document).ready(refreshFns);

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Live Search
const liveSearch = function (element) {
    let val = $(element).val().toLowerCase();
    if (!element.hasAttribute("data-target")) return false;
    let target = $(element).attr("data-target"),
        radius = $(element).dataVal("radius", 'body');
    target = $(element).parents(radius).first().find(target);
    target.filter(function () {
        let dataTarget = $(element).hasAttr("data-match") ? $(this).find($(element).attr("data-match")) : $(this),
            txt = dataTarget.text();
        if (txt) $(this).toggle(txt.toLowerCase().indexOf(val) > -1);
    });
}
//Live Search 
$(document).on("keyup", ".live-search", function () {
    liveSearch(this);
});

// Replace ${} variables
function replaceVariables(str, variables) {
    // Replace values
    for (let key in variables) {
        str = str.replace(new RegExp('\\${' + key + '}', 'gm'), variables[key]);
    }
    return str;
}
// Capitalize First Letter
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Init TinyMce 
function initTinymce() {
    if (!window.tinymce) return false;

    const CONFIG = {
        selector: '.tinymce-inline-editor',
        plugins: 'anchor autolink emoticons  link lists mentions',
        toolbar: ' bold italic underline numlist bullist undo redo | fontfamily fontsize | strikethrough | link | emoticons',
        tinycomments_mode: 'embedded',
        content_style:
            "@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap'); body { font-family: Poppins, sans-serif; }",
    };

    CONFIG.setup = (editor) => {
        const listener = (editor) => {
            let id = editor.id,
                $textarea = $(`.tinymce-inline-editor#${id}`),
                trigger = $textarea.dataVal('trigger');
            if (trigger) {
                let content = editor.getContent();
                $textarea.val(content);
                $textarea.trigger(trigger);
            }

        }

        editor.on('change', () => listener(editor));
        editor.on('input', () => listener(editor));
        editor.on("init", (e) => {
            let editor = e.target,
                items = Array.from($('.tox-tinymce-aux'));
            items.shift();

            items.map(item => {
                $(item).remove(); // Remove Tinymce extra elements
            });
            $('.tox-statusbar__branding').remove();

            $element = $(editor.targetElm);
            if ($element.hasAttr("data-height")) {
                let height = $element.dataVal("height");
                $(editor.editorContainer).css("height", height);
            }

        });
    }

    tinymce.init(CONFIG);
}

//#region Popup Window
// Show Popup Window
$(document).on("click", ".popup-window-btn,[data-toggle='popup']", function (e) {
    e.preventDefault();
    let target = $(this).attr("data-target");
    if (!target) return false;
    $(target).addClass("active");
    let event = {
        target: $(target),
        relatedTarget: $(this)
    };
    fn._handle($(target), event);
});
// Remove Popup Window
$(document).on("click", ".popup-window", function (e) {
    let target = $(e.target),
        validCloseClassSelectors = ['close-window', 'popup-window'],
        popupWindow = $(this);
    validCloseClassSelectors.forEach(function (className) {
        if (target.hasClass(className)) popupWindow.removeClass("active");
    })
});
// Close Popup Window
$(document).on("click", "[data-dismiss='popup']", function () {
    $(this).parents(".popup-window").popup("hide");
});

//#endregion Popup Window

// Download File
function downloadFile(name, url) {
    let a = document.createElement('a');
    a.style.display = 'none';
    a.download = name;
    a.href = url;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Copy Text
function copyText(text) {
    if (!navigator.clipboard) {
        // Fallback to older method
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "-9999px";
        document.body.appendChild(textArea);
        textArea.select();

        try {
            const successful = document.execCommand("copy");
            if (successful) Notify.success("Content Copied");

        } catch (error) {
            console.error("Failed to copy text to clipboard:", error);
        }

        document.body.removeChild(textArea);
    } else {
        // Use the modern clipboard API
        navigator.clipboard.writeText(text)
            .then(() => {
                Notify.success("Content Copied");
            })
            .catch((error) => {

            });
    }
}

