//#region DataTable
function DataTable() {
    $(".dataTable:not([data-launch])").each(function () {
        $(this).attr("data-launch", "true");
        // wrap controls so they sit inside the card
        let table = $(this).DataTable({
            dom: '<"dt-toolbar"lf>rt<"dt-foot"ip>',
            language: {
                search: '',
                searchPlaceholder: 'Search',
                lengthMenu: '_MENU_ per page',
                info: 'Showing _START_ to _END_ of _TOTAL_',
                infoEmpty: 'Nothing to show',
                paginate: { previous: 'Prev', next: 'Next' }
            }
        });
        // Filters
        if ($(this).hasAttr("data-filters") || false) {
            $(this).find("thead tr th, thead tr td").each(function (i) {
                let parent = $(this).parents(".table-responsive");
                if (parent.find(".filter-row").length < 1) parent.prepend('<div class="row filter-row pb-3"></div>');

                let filtersParent = parent.find(".filter-row"),
                    name = $(this).text();
                //lists for search
                if ($(this).hasAttr("data-filter")) {
                    let columnData = [];
                    data = table.column(i).data(),
                        selector = 'table-filter-' + name.replace(/[^a-zA-Z]/g, '');
                    for (let j = 0; j < data.length; j++) {
                        let cData = data[j];
                        if (!columnData.includes(cData))
                            columnData.push(cData)
                    }
                    let filterType = $(this).data("filter"),
                        element = '',
                        event = "change";
                    if (filterType === "list") {
                        let listItems = '',
                            listItemsArr = [];
                        columnData.forEach(item => {
                            let items = filterItems(item);
                            if (items === false) {
                                item = stripTags(item);
                                items = [item];
                            }
                            items.forEach(fItem => {
                                fItem = fItem.trim();
                                let itemText = capitalizeFirstLetter(fItem);
                                if (!listItemsArr.includes(fItem)) {
                                    listItems += `<option value="${fItem}">${itemText}</option>`;
                                    listItemsArr.push(fItem);
                                }
                            })
                        });
                        element = `<select class="form-control ${selector}">
                                        <option value="">-- Select --</option>
                                        ${listItems}
                                    </select>`;
                    } else if (filterType === "date") {
                        element = `<input type="date" class="form-control date_input ${selector}">`;
                    }
                    let col = 'col-lg-3';
                    if ($(this).data("col")) col = $(this).data("col");
                    let list = `
                    <div class="${col}">
                    <span class="label">${name}</span>
                    ${element}
                    </div>`;
                    filtersParent.append(list);

                    // Add filter functionality
                    $('.' + selector).on('change', function () {
                        setTimeout(() => {
                            if (table.column(i).search() !== this.value) {
                                table
                                    .column(i)
                                    .search(this.value)
                                    .draw();
                            }
                        }, 500);
                    });
                }
            });
        }
    });

};
//#endregion DataTable
