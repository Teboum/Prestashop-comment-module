/* global $ */
$(document).ready(function () {
  var $searchWidget = $("#search_widget");
  var $searchBox = $searchWidget.find("input[type=text]");
  var searchURL = $searchWidget.attr("data-search-controller-url");
  var $clearButton = $searchWidget.find("i.clear");

  $.widget("prestashop.psBlockSearchAutocomplete", $.ui.autocomplete, {
    _renderItem: function (ul, product) {
      var image = product.cover
        ? product.cover
        : prestashop.urls.no_picture_image;
      var $img = $(
        '<img class="autocomplete-thumbnail" src="' +
          image.bySize.small_default.url +
          '">'
      );
      return $("<li>")
        .append(
          $("<a>")
            .append($img)
            .append($("<span>").html(product.name).addClass("product"))
        )
        .appendTo(ul);
    },
  });

  var isMobile = function () {
    return $(window).width() < 768;
  };
  var autocompletePosition = function () {
    return {
      my: "right top",
      at: "right bottom",
      of: isMobile() ? ".header-top" : "#search_widget",
    };
  };

  $searchBox
    .psBlockSearchAutocomplete({
      position: autocompletePosition(),
      source: function (query, response) {
        $.post(
          searchURL,
          {
            s: query.term,
            resultsPerPage: 10,
          },
          null,
          "json"
        )
          .then(function (resp) {
            response(resp.products);
          })
          .fail(response);
      },
      select: function (event, ui) {
        var url = ui.item.url;
        window.location.href = url;
      },
    })
    .psBlockSearchAutocomplete("widget")
    .addClass("searchbar-autocomplete");

  $(window).resize(function () {
    $searchBox.psBlockSearchAutocomplete({
      position: autocompletePosition(),
    });
    $searchBox.keyup();
  });

  $clearButton.click(function () {
    $searchBox.val("");
    $clearButton.hide();
  });

  $searchBox.keyup(function () {
    $clearButton.toggle($searchBox.val() !== "" && isMobile());
  });
});

function displaySearchInput(e) {
  const searchIcon = document.getElementById("searchIcon");
  const searchInput = document.getElementById("searchInput");
  if (searchIcon.style.display !== "none") {
    searchInput.style.display = "inline-block";
    searchInput.focus();
    searchIcon.style.display = "none";
  }
}
var setTimeoutFunc;
function hideSearchInput(e) {
  if (!e.value) {
    const searchIcon = document.getElementById("searchIcon");
    const searchInput = document.getElementById("searchInput");
    setTimeoutFunc = setTimeout(function () {
      searchInput.style.display = "none";
      searchIcon.style.display = "block";
    }, 2000);
  }
}
function handleFocus(e) {
  const searchInput = document.getElementById("searchInput");
  if (setTimeoutFunc) {
    clearTimeout(setTimeoutFunc);
  }
}
