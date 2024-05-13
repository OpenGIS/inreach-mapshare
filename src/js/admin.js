//Tooltips
window.joe_setup_parameter_tooltips = function () {
  jQuery("a.inmap-tooltip").on({
    mouseenter: function (e) {
      var title = jQuery(this).data("title");
      jQuery('<p id="inmap-tooltip-active"></p>')
        .text(title)
        .appendTo("body")
        .fadeIn("slow");
    },
    mouseleave: function (e) {
      jQuery("#inmap-tooltip-active").remove();
    },
    mousemove: function (e) {
      if (joe_is_touch_device()) {
        var mousex = e.pageX - 250;
      } else {
        var mousex = e.pageX - 220;
      }

      var mousey = e.pageY + 5;
      jQuery("#inmap-tooltip-active").css({ top: mousey, left: mousex });
    },
  });
};

//Touch device?
//Thanks https://stackoverflow.com/questions/4817029/whats-the-best-way-to-detect-a-touch-screen-device-using-javascript/4819886#4819886
window.joe_is_touch_device = function () {
  var prefixes = " -webkit- -moz- -o- -ms- ".split(" ");
  var mq = function (media_qry) {
    return window.matchMedia(media_qry).matches;
  };

  if (
    "ontouchstart" in window ||
    (window.DocumentTouch && document instanceof DocumentTouch)
  ) {
    return true;
  }

  // include the 'heartz' as a way to have a non matching MQ to help terminate the join
  // https://git.io/vznFH
  var media_qry = ["(", prefixes.join("touch-enabled),("), "heartz", ")"].join(
    "",
  );
  return mq(media_qry);
};

window.joe_setup_accordions = function () {
  var accordion_container = jQuery(".inmap-accordion-container");

  if (!accordion_container.length) {
    return;
  }

  accordion_container.addClass("inmap-self-clear");

  //For each accordion
  accordion_container.each(function () {
    //Hide all but first initially
    var group_index = 0;

    //Each group
    jQuery(".inmap-accordion-group", jQuery(this)).each(function () {
      var group = jQuery(this);

      group.addClass("inmap-self-clear");
      group.data("inmap-index", group_index);

      var group_content = jQuery(".inmap-accordion-group-content", group);

      //Show first
      if (group_index == 0) {
        group.addClass("inmap-first inmap-active");

        group_content.show().addClass(group_index);
        //Hide others
      } else {
        group_content.hide().addClass(group_index);
      }

      //Each legend
      jQuery("legend", jQuery(this)).each(function () {
        //Append text to legend (if not already exists)
        var legend_html = jQuery(this).html();
        if (
          legend_html.indexOf("[+]") == -1 &&
          legend_html.indexOf("[-]") == -1
        ) {
          var text = group_index == 0 ? "[-]" : "[+]";
          jQuery(this).html(legend_html + " <span>" + text + "</span>");
        }

        //Slide
        jQuery(this).click(function () {
          var clicked_group_index = jQuery(this)
            .parents(".inmap-accordion-group")
            .data("inmap-index");

          //For each parameter group
          jQuery(
            ".inmap-accordion-group",
            jQuery(this).parents(".inmap-accordion-container"),
          ).each(function () {
            //If this was clicked
            if (jQuery(this).data("inmap-index") == clicked_group_index) {
              var legend = jQuery("legend", jQuery(this));

              //Is it active?
              if (jQuery(this).hasClass("inmap-active")) {
                legend.html(legend.html().replace("[-]", "[+]"));

                jQuery(this).removeClass("inmap-active");

                jQuery(
                  ".inmap-accordion-group-content",
                  jQuery(this),
                ).slideUp();
                //Not active (yet)
              } else {
                legend.html(legend.html().replace("[+]", "[-]"));

                jQuery(this).addClass("inmap-active");

                jQuery(
                  ".inmap-accordion-group-content",
                  jQuery(this),
                ).slideDown();
              }
              //Hide others
            } else {
              jQuery(this).removeClass("inmap-active");

              var legend = jQuery("legend", jQuery(this));
              legend.html(legend.html().replace("[-]", "[+]"));

              jQuery(".inmap-accordion-group-content", jQuery(this)).slideUp();
            }
          });
        });
      });

      group_index++;
    });
  });
};

window.joe_setup_repeatable_settings = function () {
  //Each container
  jQuery(".inmap-settings-tab .inmap-repeatable").each(function () {
    var container = jQuery(this);

    //Each form table
    jQuery(".form-table", container).each(function () {
      var form = jQuery(this);
      var clones = [];

      form.remove();

      //Each input
      jQuery(".inmap-input", form).each(function () {
        var input = jQuery(this);
        //Copy ID to class
        input.addClass("inmap-" + input.data("id"));

        //Get values
        if (input.get(0).nodeName != "SELECT") {
          var values = input.val();
        } else {
          var values = input.data("multi-value");
        }

        //Ensure is string
        if (typeof values != "string") {
          values = values.toString();
        }

        //Determine clone values
        values = values.split(joe_admin_js.multi_value_seperator);
        for (i in values) {
          if (typeof clones[i] !== "object") {
            clones[i] = {};
          }
          clones[i][input.data("id")] = values[i];
        }
      });

      //Each clone
      for (i = 0; i < clones.length; i++) {
        var clone = form.clone();

        //Create input
        for (j in clones[i]) {
          var set_value = clones[i][j];

          var input = jQuery(".inmap-input-" + j, clone);
          input.attr("name", input.attr("name") + "[" + i + "]");

          //This is a Select without a valid option
          if (
            input.get(0).nodeName == "SELECT" &&
            !jQuery("option[value='" + clones[i][j] + "']", input).length
          ) {
            //Use first as default
            set_value = jQuery("option", input).first().val();
          }

          //Set value
          input.attr("value", set_value).val(set_value);

          //Make uneditable
          if (
            input.parents(".inmap-control-group").hasClass("inmap-uneditable")
          ) {
            input.attr("readonly", "readonly");
          }
        }

        //Delete button
        var delete_button = jQuery("<div />")
          .text("x")
          .attr("title", joe_admin_js.lang.repeatable_delete_title)
          .addClass("inmap-delete")
          .on("click", function (e) {
            e.preventDefault();

            var form = jQuery(this).parents(".form-table");
            form.remove();

            return false;
          });
        clone.append(delete_button);

        container.append(clone);
        container.attr("data-count", i);
        joe_setup_parameter_tooltips();
      }

      var add_button = jQuery("<button />")
        .html('<i class="ion ion-plus"></i>')
        .addClass("button inmap-add")
        .on("click", function (e) {
          e.preventDefault();

          //Increment count
          var container = jQuery(this).parents(".inmap-repeatable");
          var count_old = parseInt(container.attr("data-count"));
          var count_new = count_old + 1;
          container.attr("data-count", count_new);

          //Modify clone
          var clone = form.clone();
          jQuery(".inmap-input", clone).each(function () {
            var input = jQuery(this);
            var input_name = input.attr("name") + "[" + count_new + "]";

            //Update
            input.attr("name", input_name);
            input.attr("placeholder", "");

            //Clear text inputs
            if (input.get(0).nodeName != "SELECT") {
              input.val("");
            }
          });

          jQuery(this).before(clone);

          joe_setup_parameter_tooltips();
          joe_setup_colour_pickers();

          return false;
        });
      container.append(add_button);
      //form.wrap(container);
      container.sortable();
    });
  });
};

window.joe_setup_dropdowns = function () {
  jQuery(".inmap-parameters-container").each(function () {
    var container = jQuery(this);

    jQuery("select", container).each(function () {
      //Prefix
      var class_string = "inmap-dropdown-" + jQuery(this).data("id") + "-";

      //Add new
      class_string += jQuery(this).val();
      container.addClass(class_string);

      //On Change
      jQuery(this).on("change", function () {
        //Prefix
        var class_string = "inmap-dropdown-" + jQuery(this).data("id") + "-";

        //Remove old
        jQuery("option", jQuery(this)).each(function () {
          container.removeClass(class_string + jQuery(this).attr("value"));
        });

        //Add new
        class_string += jQuery(this).val();
        container.addClass(class_string);
      });
    });
  });
};

window.joe_setup_settings_nav = function () {
  var nav_container = jQuery("body.wp-admin #inmap-settings-nav");

  if (!nav_container) {
    return false;
  }

  var admin_container = jQuery("#inmap-admin-container");
  var form = jQuery("form", admin_container);

  var tabs = jQuery(".inmap-settings-tab", admin_container);
  var init_tab_key = nav_container.data("init_tab_key");

  //Change
  var select = jQuery("select", nav_container);
  select.hover(
    function () {
      jQuery(this).attr("size", jQuery("option", jQuery(this)).length);
    },
    function () {
      jQuery(this).removeAttr("size");
    },
  );

  select.change(function () {
    select.removeAttr("size");

    var selected_content_id = jQuery(this).val();
    admin_container.attr("class", "");

    //Update form redirect
    var redirect_input = jQuery('input[name="_wp_http_referer"]', form);
    var redirect_to = document.location.toString();
    if (redirect_to.indexOf("content=") > 0) {
      redirect_to = redirect_to.replace(
        "content=" + init_tab_key,
        "content=" + selected_content_id,
      );
    } else {
      redirect_to = redirect_to + "&content=" + selected_content_id;
    }
    redirect_input.val(redirect_to);

    var show_content = jQuery("." + selected_content_id).first();

    //Each Tab
    jQuery(".inmap-settings-tab").each(function () {
      var tab = jQuery(this);
      tab.hide();

      //Entire Tab
      if (selected_content_id.indexOf("settings-tab")) {
        //Selected
        if (tab.hasClass(selected_content_id)) {
          tab.show();
          admin_container.addClass("inmap-active-" + selected_content_id);
        }
      }

      //Each Section
      jQuery(".inmap-settings-section", tab).each(function () {
        var section = jQuery(this);

        if (selected_content_id.indexOf("settings-tab") > 0) {
          section.show();
        } else if (selected_content_id.indexOf("settings-section") > 0) {
          section.hide();

          //Selected
          if (section.hasClass(selected_content_id)) {
            tab.show();
            section.show();
            admin_container.addClass("inmap-active-" + selected_content_id);
          }
        }
      });
    });
  });
  select.trigger("change");
};

window.joe_admin_message = function (
  message = null,
  type = "info",
  container_selector = "#inmap-admin-container .card",
) {
  if (message) {
    switch (type) {
      //      case 'error' :
      //
      //        break;
      default:
        //      case 'info' :

        break;
    }

    //Get container
    var container = jQuery(container_selector).first();

    //Fallback
    if (!container.length) {
      var container = jQuery("#wpbody-content").first();
    }

    //Container exists
    if (container.length) {
      //Remove *all* existing
      jQuery(".notice").each(function () {
        jQuery(this).remove();
      });

      var notice_div = jQuery("<div />").attr({
        class: "inmap-notice notice notice-" + type,
      });
      var notice_p = jQuery("<p />").html(message);
      //Put together
      notice_div.append(notice_p);

      //Display
      container.prepend(notice_div);
    } else {
      alert(message);
    }
  }
};

window.joe_setup_colour_pickers = function () {
  jQuery(".inmap-colour-picker .inmap-input").wpColorPicker();
};

jQuery(document).ready(function () {
  joe_setup_parameter_tooltips();
  joe_setup_accordions();

  joe_setup_settings_nav();
  joe_setup_repeatable_settings();
  joe_setup_dropdowns();
  joe_setup_colour_pickers();
});
