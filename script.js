

$(document).ready(() => {

    var telegramWidget = $("<script>");

    telegramWidget.attr({
        "async": true,
        "src": "https://telegram.org/js/telegram-widget.js?7",
        "data-telegram-login": botusername,
        "data-size": "large",
        "data-radius": "5",
        "data-auth-url": M.cfg.wwwroot + "/auth/telegram/index.php"
    });

    var identityproviders = $('.row.no-gutters.mt-1');

    if (identityproviders.length > 0) {
        var identityprovidersContainer = $('<div>');

        identityprovidersContainer.addClass('btn btn-secondary login-identityprovider-btn rui-potentialidp w-100 mt-1');

        identityprovidersContainer.css('background-color', '#54a9eb');

        identityprovidersContainer.append(telegramWidget);

        identityproviders.append(identityprovidersContainer);
    }
})