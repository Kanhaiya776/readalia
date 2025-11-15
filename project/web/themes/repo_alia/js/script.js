(function ($, Drupal) {
  // Menu toggle behavior.
  Drupal.behaviors.menuToggle = {
    attach: function (context, settings) {
      $("#cssmenu", context).prepend('<div id="menu-button">Menu</div>');
      $("#cssmenu #menu-button", context).on("click", function () {
        var menu = $(this).next("ul");
        if (menu.hasClass("open")) {
          menu.removeClass("open");
        } else {
          menu.addClass("open");
        }
      });
    },
  };

  // Search validation behavior.
  Drupal.behaviors.searchValidation = {
    attach: function (context, settings) {
      var $input = $('input[name="search_api_fulltext"]', context);
      if (typeof $.fn.once !== "undefined") {
        $input = $input.once("searchValidation");
      }
      $input.on("input", function () {
        var value = $(this).val();
        if (!/^[a-zA-Z0-9\s]*$/.test(value)) {
          $(this).val("");
          Drupal.announce("Special characters are not allowed.");
        }
      });
    },
  };
  // label change for view filter
  Drupal.behaviors.changeSearchOptionLabel = {
    attach: function (context, settings) {
      $('select[name="search_api_fulltext_op"]', context).each(function () {
        if (!$(this).hasClass("changeSearchOptionLabel-processed")) {
          $(this).addClass("changeSearchOptionLabel-processed"); 
          $(this)
            .find("option")
            .each(function () {
              const originalText = $(this).text().trim();
              if (originalText === "Contains all of these words") {
                $(this).text("Contains exact phrase");
              }
            });
        }
      });
    },
  };
})(jQuery, Drupal);