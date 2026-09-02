const c_location = location.href;
let c_link = c_location.split("/").pop();
let controllers = {};
const menu_links = $(".sidebar .nav .nav-link");
menu_links.parent().removeClass('active');

Array.from(menu_links).forEach((link) => {
    let href = $(link).attr("href").split("/").pop();
    if (href === c_link) {
        if ($(link).parents(".dropdown").length > 0) {
            $(link).parents(".dropdown").addClass('active');
            $(link).parent().parent().show();
        }
        $(link).parent().addClass("active");
    } else {
        $(link).parent().removeClass('active');
    }
});
// Active sidebar link
function activeSidebarLink() {
    let url = window.location.href.split("/").pop(),
        $li = $(`.sidebar .nav .nav-link[href="${url}"]`).parent(),
        $parent = $li.parents(".nav-item");

    if ($parent.length) {
        if ($parent.hasClass("with-sub-menu"))
            $parent.find(".nav-link").first().trigger("click");
    }

    $li.addClass("active");
    $parent.removeClass('active');
}
$(document).ready(activeSidebarLink);
// Nav item with sub menu
$(document).on("click", ".sidebar .nav .nav-item.with-sub-menu > .nav-link", function (e) {
    e.preventDefault();
    let $li = $(this).parent();
    let $submenu = $li.find(".sub-menu");
    $submenu.slideToggle(300);
    $li.toggleClass("active", !$li.is(".active"));
});