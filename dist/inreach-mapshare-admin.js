window.inmap_setup_parameter_tooltips = function () {
  jQuery("a.inmap-tooltip").on({
    mouseenter: function (e) {
      var t = jQuery(this).data("title");
      jQuery('<p id="inmap-tooltip-active"></p>')
        .text(t)
        .appendTo("body")
        .fadeIn("slow");
    },
    mouseleave: function (e) {
      jQuery("#inmap-tooltip-active").remove();
    },
    mousemove: function (e) {
      if (inmap_is_touch_device()) var t = e.pageX - 250;
      else var t = e.pageX - 220;
      var a = e.pageY + 5;
      jQuery("#inmap-tooltip-active").css({ top: a, left: t });
    },
  });
};
window.inmap_is_touch_device = function () {
  var e = " -webkit- -moz- -o- -ms- ".split(" "),
    t = function (u) {
      return window.matchMedia(u).matches;
    };
  if (
    "ontouchstart" in window ||
    (window.DocumentTouch && document instanceof DocumentTouch)
  )
    return !0;
  var a = ["(", e.join("touch-enabled),("), "heartz", ")"].join("");
  return t(a);
};
window.inmap_setup_accordions = function () {
  var e = jQuery(".inmap-accordion-container");
  e.length &&
    (e.addClass("inmap-self-clear"),
    e.each(function () {
      var t = 0;
      jQuery(".inmap-accordion-group", jQuery(this)).each(function () {
        var a = jQuery(this);
        a.addClass("inmap-self-clear"), a.data("inmap-index", t);
        var u = jQuery(".inmap-accordion-group-content", a);
        t == 0
          ? (a.addClass("inmap-first inmap-active"), u.show().addClass(t))
          : u.hide().addClass(t),
          jQuery("legend", jQuery(this)).each(function () {
            var p = jQuery(this).html();
            if (p.indexOf("[+]") == -1 && p.indexOf("[-]") == -1) {
              var n = t == 0 ? "[-]" : "[+]";
              jQuery(this).html(p + " <span>" + n + "</span>");
            }
            jQuery(this).click(function () {
              var c = jQuery(this)
                .parents(".inmap-accordion-group")
                .data("inmap-index");
              jQuery(
                ".inmap-accordion-group",
                jQuery(this).parents(".inmap-accordion-container"),
              ).each(function () {
                if (jQuery(this).data("inmap-index") == c) {
                  var o = jQuery("legend", jQuery(this));
                  jQuery(this).hasClass("inmap-active")
                    ? (o.html(o.html().replace("[-]", "[+]")),
                      jQuery(this).removeClass("inmap-active"),
                      jQuery(
                        ".inmap-accordion-group-content",
                        jQuery(this),
                      ).slideUp())
                    : (o.html(o.html().replace("[+]", "[-]")),
                      jQuery(this).addClass("inmap-active"),
                      jQuery(
                        ".inmap-accordion-group-content",
                        jQuery(this),
                      ).slideDown());
                } else {
                  jQuery(this).removeClass("inmap-active");
                  var o = jQuery("legend", jQuery(this));
                  o.html(o.html().replace("[-]", "[+]")),
                    jQuery(
                      ".inmap-accordion-group-content",
                      jQuery(this),
                    ).slideUp();
                }
              });
            });
          }),
          t++;
      });
    }));
};
window.inmap_setup_repeatable_settings = function () {
  jQuery(".inmap-settings-tab .inmap-repeatable").each(function () {
    var e = jQuery(this);
    jQuery(".form-table", e).each(function () {
      var t = jQuery(this),
        a = [];
      for (
        t.remove(),
          jQuery(".inmap-input", t).each(function () {
            var s = jQuery(this);
            if (
              (s.addClass("inmap-" + s.data("id")),
              s.get(0).nodeName != "SELECT")
            )
              var r = s.val();
            else var r = s.data("multi-value");
            typeof r != "string" && (r = r.toString()),
              (r = r.split(inmap_admin_js.multi_value_seperator));
            for (i in r)
              typeof a[i] != "object" && (a[i] = {}),
                (a[i][s.data("id")] = r[i]);
          }),
          i = 0;
        i < a.length;
        i++
      ) {
        var u = t.clone();
        for (j in a[i]) {
          var p = a[i][j],
            n = jQuery(".inmap-input-" + j, u);
          n.attr("name", n.attr("name") + "[" + i + "]"),
            n.get(0).nodeName == "SELECT" &&
              !jQuery("option[value='" + a[i][j] + "']", n).length &&
              (p = jQuery("option", n).first().val()),
            n.attr("value", p).val(p),
            n.parents(".inmap-control-group").hasClass("inmap-uneditable") &&
              n.attr("readonly", "readonly");
        }
        var c = jQuery("<div />")
          .text("x")
          .attr("title", inmap_admin_js.lang.repeatable_delete_title)
          .addClass("inmap-delete")
          .on("click", function (s) {
            s.preventDefault();
            var r = jQuery(this).parents(".form-table");
            return r.remove(), !1;
          });
        u.append(c),
          e.append(u),
          e.attr("data-count", i),
          inmap_setup_parameter_tooltips();
      }
      var o = jQuery("<button />")
        .html('<i class="ion ion-plus"></i>')
        .addClass("button inmap-add")
        .on("click", function (s) {
          s.preventDefault();
          var r = jQuery(this).parents(".inmap-repeatable"),
            h = parseInt(r.attr("data-count")),
            m = h + 1;
          r.attr("data-count", m);
          var l = t.clone();
          return (
            jQuery(".inmap-input", l).each(function () {
              var d = jQuery(this),
                y = d.attr("name") + "[" + m + "]";
              d.attr("name", y),
                d.attr("placeholder", ""),
                d.get(0).nodeName != "SELECT" && d.val("");
            }),
            jQuery(this).before(l),
            inmap_setup_parameter_tooltips(),
            inmap_setup_colour_pickers(),
            !1
          );
        });
      e.append(o), e.sortable();
    });
  });
};
window.inmap_setup_dropdowns = function () {
  jQuery(".inmap-parameters-container").each(function () {
    var e = jQuery(this);
    jQuery("select", e).each(function () {
      var t = "inmap-dropdown-" + jQuery(this).data("id") + "-";
      (t += jQuery(this).val()),
        e.addClass(t),
        jQuery(this).on("change", function () {
          var a = "inmap-dropdown-" + jQuery(this).data("id") + "-";
          jQuery("option", jQuery(this)).each(function () {
            e.removeClass(a + jQuery(this).attr("value"));
          }),
            (a += jQuery(this).val()),
            e.addClass(a);
        });
    });
  });
};
window.inmap_setup_settings_nav = function () {
  var e = jQuery("body.wp-admin #inmap-settings-nav");
  if (!e) return !1;
  var t = jQuery("#inmap-admin-container"),
    a = jQuery("form", t);
  jQuery(".inmap-settings-tab", t);
  var u = e.data("init_tab_key"),
    p = jQuery("select", e);
  p.hover(
    function () {
      jQuery(this).attr("size", jQuery("option", jQuery(this)).length);
    },
    function () {
      jQuery(this).removeAttr("size");
    },
  ),
    p.change(function () {
      p.removeAttr("size");
      var n = jQuery(this).val();
      t.attr("class", "");
      var c = jQuery('input[name="_wp_http_referer"]', a),
        o = document.location.toString();
      o.indexOf("content=") > 0
        ? (o = o.replace("content=" + u, "content=" + n))
        : (o = o + "&content=" + n),
        c.val(o),
        jQuery("." + n).first(),
        jQuery(".inmap-settings-tab").each(function () {
          var s = jQuery(this);
          s.hide(),
            n.indexOf("settings-tab") &&
              s.hasClass(n) &&
              (s.show(), t.addClass("inmap-active-" + n)),
            jQuery(".inmap-settings-section", s).each(function () {
              var r = jQuery(this);
              n.indexOf("settings-tab") > 0
                ? r.show()
                : n.indexOf("settings-section") > 0 &&
                  (r.hide(),
                  r.hasClass(n) &&
                    (s.show(), r.show(), t.addClass("inmap-active-" + n)));
            });
        });
    }),
    p.trigger("change");
};
window.inmap_admin_message = function (
  e = null,
  t = "info",
  a = "#inmap-admin-container .card",
) {
  if (e) {
    var u = jQuery(a).first();
    if (!u.length) var u = jQuery("#wpbody-content").first();
    if (u.length) {
      jQuery(".notice").each(function () {
        jQuery(this).remove();
      });
      var p = jQuery("<div />").attr({
          class: "inmap-notice notice notice-" + t,
        }),
        n = jQuery("<p />").html(e);
      p.append(n), u.prepend(p);
    } else alert(e);
  }
};
window.inmap_setup_colour_pickers = function () {
  jQuery(".inmap-colour-picker .inmap-input").wpColorPicker();
};
jQuery(document).ready(function () {
  jQuery("body").hasClass("settings_page_inreach-mapshare-settings") &&
    (inmap_setup_parameter_tooltips(),
    inmap_setup_accordions(),
    inmap_setup_settings_nav(),
    inmap_setup_repeatable_settings(),
    inmap_setup_dropdowns(),
    inmap_setup_colour_pickers());
});
