window.inmap_maps = [];
window.inmap_create_map = function (e = null, t = null, a = null) {
	if (!e || !t || !jQuery || typeof inmap_L != "object") return !1;
	var f = { attributionControl: !1 },
		d = "inmap-" + e,
		n = jQuery("#" + d);
	if (!n.length) return !1;
	var m = inmap_L.map(d, f),
		l = inmap_L.control.attribution({ prefix: !1 });
	l.addAttribution(
		'<a href="https://github.com/OpenGIS/inreach-mapshare">Inreach Mapshare</a>',
	),
		l.addTo(m),
		n.data("map_l", m),
		(inmap_maps[e] = m);
	var p = jQuery("body").first(),
		o = n.parents(".inmap-wrap", p),
		y = jQuery(".inmap-info", o),
		h = {},
		_ = {},
		s = {},
		g = {},
		b = {},
		S = {},
		N = function () {
			var r = y.height(),
				c = g.height(),
				u = r - c;
			u > 0 ? y.css("padding-top", c + "px") : y.removeAttr("style");
		},
		F = function () {
			E(),
				z(),
				jQuery(window).on("resize", function () {
					w();
				}),
				w();
		},
		w = function () {
			o.hasClass("inmap-fullscreen")
				? o.css({ width: p.width() + "px", height: p.height() + "px" })
				: o.removeAttr("style"),
				N(),
				typeof b.get == "function" &&
					b.get(0).scrollIntoView({ behaviour: "smooth", block: "center" }),
				m.invalidateSize();
		},
		z = function () {
			S = jQuery(".leaflet-control-container .leaflet-top", n).first();
			var r = jQuery("<div />")
				.attr({ class: "inmap-control leaflet-bar leaflet-control" })
				.append(
					jQuery("<a />")
						.attr({
							class: "inmap-button inmap-icon inmap-icon-fullscreen",
							href: "#",
							title: "Fullscreen",
							role: "button",
							"aria-label": "Fullscreen",
						})
						.on("click", function (c) {
							c.preventDefault(),
								p.toggleClass("inmap-has-single"),
								o.toggleClass("inmap-fullscreen"),
								w();
						}),
				);
			S.addClass("inmap-map-ui").append(r);
		},
		E = function () {
			for (id in s) {
				var r = jQuery(".inmap-info-title", s[id]),
					c = r.text().replace("[", "<span>").replace("]", "</span>");
				r.html(c),
					s[id].hasClass("inmap-only") || s[id].addClass("inmap-hide-extended");
				var u = jQuery(".inmap-icon", _[id]);
				s[id].append(u.clone()),
					jQuery("table tr", s[id]).each(function () {
						var v = jQuery(this),
							x = jQuery("td", v);
						jQuery("th", v).addClass("inmap-info-extended");
						var D = v.attr("class").replace("inmap-assoc_array-", ""),
							q = x.text();
						switch (D) {
							case "elevation":
								var k = parseFloat(q);
								isNaN(k) ||
									x.text(
										k.toFixed(1) +
											" (m) " +
											(k * 3.28084).toFixed(1) +
											" (ft) ",
									);
								break;
							case "velocity":
								var L = parseFloat(q);
								isNaN(L) ||
									x.text(
										L.toFixed(1) +
											" (km/h) " +
											(L * 1.609344).toFixed(1) +
											" (mph) ",
									);
								break;
							case "time_utc":
							case "valid_gps_fix":
								v.addClass("inmap-info-extended");
								break;
						}
					}),
					s[id].hasClass("inmap-last") && (g = s[id]);
			}
		},
		Q = function (r = null, c = "active", u = !1) {
			var v = 14;
			for (this_id in h)
				this_id === r
					? (c == "active" && s[this_id].hasClass("inmap-active")
							? (s[this_id].removeClass("inmap-hide-extended"),
								m.getZoom() < v
									? m.setView(h[this_id].getLatLng(), v)
									: m.setView(h[this_id].getLatLng()))
							: (_[this_id].addClass("inmap-" + c),
								s[this_id].addClass("inmap-" + c)),
						c == "active" &&
							(u &&
								s[this_id]
									.get(0)
									.scrollIntoView({ behaviour: "smooth", block: "center" }),
							m.setView(h[this_id].getLatLng())))
					: c == "active"
						? (s[this_id].removeClass("inmap-active"),
							s[this_id].hasClass("inmap-last") ||
								_[this_id].removeClass("inmap-active"))
						: (s[this_id].removeClass("inmap-" + c),
							_[this_id].removeClass("inmap-" + c));
			c == "active" && N();
		},
		I = function (r) {
			inmap_L
				.geoJSON(r, {
					style: { color: "#ff7800", weight: 3, opacity: 0.5 },
					onEachFeature: function (c, u) {
						u.bindTooltip("Planned Route");
					},
				})
				.addTo(m);
		},
		T = "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
	if (
		typeof inmap_shortcode_js.basemap_url == "string" &&
		inmap_shortcode_js.basemap_url.length
	)
		var T = inmap_shortcode_js.basemap_url;
	var O =
		'&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';
	if (
		typeof inmap_shortcode_js.basemap_attribution == "string" &&
		inmap_shortcode_js.basemap_attribution.length
	)
		var O = inmap_shortcode_js.basemap_attribution;
	inmap_L.tileLayer(T, { maxZoom: 19, attribution: O }).addTo(m);
	var C = inmap_L.geoJSON(t, {
		style: function (r) {
			if (typeof r.properties.style == "object") return r.properties.style;
		},
		pointToLayer: function (r, c) {
			if (typeof r.properties.id > "u") return !1;
			var u = r.properties.id.toString();
			return (
				typeof r.properties.icon == "object"
					? (h[u] = inmap_L.marker(c, {
							icon: inmap_L.divIcon(r.properties.icon),
						}))
					: (h[u] = inmap_L.marker(c)),
				(s[u] = jQuery("<div />")
					.addClass(r.properties.className)
					.attr("title", r.properties.title)
					.html(r.properties.description)
					.hover(
						function () {
							Q(u, "hover");
						},
						function () {
							Q(null, "hover");
						},
					)
					.on("click", function () {
						(b = jQuery(this)), Q(u, "active");
					})),
				y.append(s[u]),
				h[u]
			);
		},
		onEachFeature: function (r, c) {
			if (typeof r.properties.id > "u") return !1;
			var u = r.properties.id.toString();
			c.on("add", function (v) {
				_[u] = jQuery(v.target.getElement())
					.attr("title", r.properties.title)
					.addClass(r.properties.className)
					.data("marker_l", v.target)
					.on("mouseenter", function () {
						Q(u, "hover", !0);
					})
					.on("click", function () {
						Q(u, "active", !0);
					});
			});
		},
	});
	C.addTo(m),
		C.on("add", function () {
			F();
		}),
		m.fitBounds(C.getBounds()),
		a && JSON.stringify(a) && I(a);
};
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
		t = function (f) {
			return window.matchMedia(f).matches;
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
				var f = jQuery(".inmap-accordion-group-content", a);
				t == 0
					? (a.addClass("inmap-first inmap-active"), f.show().addClass(t))
					: f.hide().addClass(t),
					jQuery("legend", jQuery(this)).each(function () {
						var d = jQuery(this).html();
						if (d.indexOf("[+]") == -1 && d.indexOf("[-]") == -1) {
							var n = t == 0 ? "[-]" : "[+]";
							jQuery(this).html(d + " <span>" + n + "</span>");
						}
						jQuery(this).click(function () {
							var m = jQuery(this)
								.parents(".inmap-accordion-group")
								.data("inmap-index");
							jQuery(
								".inmap-accordion-group",
								jQuery(this).parents(".inmap-accordion-container"),
							).each(function () {
								if (jQuery(this).data("inmap-index") == m) {
									var l = jQuery("legend", jQuery(this));
									jQuery(this).hasClass("inmap-active")
										? (l.html(l.html().replace("[-]", "[+]")),
											jQuery(this).removeClass("inmap-active"),
											jQuery(
												".inmap-accordion-group-content",
												jQuery(this),
											).slideUp())
										: (l.html(l.html().replace("[+]", "[-]")),
											jQuery(this).addClass("inmap-active"),
											jQuery(
												".inmap-accordion-group-content",
												jQuery(this),
											).slideDown());
								} else {
									jQuery(this).removeClass("inmap-active");
									var l = jQuery("legend", jQuery(this));
									l.html(l.html().replace("[-]", "[+]")),
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
						var p = jQuery(this);
						if (
							(p.addClass("inmap-" + p.data("id")),
							p.get(0).nodeName != "SELECT")
						)
							var o = p.val();
						else var o = p.data("multi-value");
						typeof o != "string" && (o = o.toString()),
							(o = o.split(inmap_admin_js.multi_value_seperator));
						for (i in o)
							typeof a[i] != "object" && (a[i] = {}),
								(a[i][p.data("id")] = o[i]);
					}),
					i = 0;
				i < a.length;
				i++
			) {
				var f = t.clone();
				for (j in a[i]) {
					var d = a[i][j],
						n = jQuery(".inmap-input-" + j, f);
					n.attr("name", n.attr("name") + "[" + i + "]"),
						n.get(0).nodeName == "SELECT" &&
							!jQuery("option[value='" + a[i][j] + "']", n).length &&
							(d = jQuery("option", n).first().val()),
						n.attr("value", d).val(d),
						n.parents(".inmap-control-group").hasClass("inmap-uneditable") &&
							n.attr("readonly", "readonly");
				}
				var m = jQuery("<div />")
					.text("x")
					.attr("title", inmap_admin_js.lang.repeatable_delete_title)
					.addClass("inmap-delete")
					.on("click", function (p) {
						p.preventDefault();
						var o = jQuery(this).parents(".form-table");
						return o.remove(), !1;
					});
				f.append(m),
					e.append(f),
					e.attr("data-count", i),
					inmap_setup_parameter_tooltips();
			}
			var l = jQuery("<button />")
				.html('<i class="ion ion-plus"></i>')
				.addClass("button inmap-add")
				.on("click", function (p) {
					p.preventDefault();
					var o = jQuery(this).parents(".inmap-repeatable"),
						y = parseInt(o.attr("data-count")),
						h = y + 1;
					o.attr("data-count", h);
					var _ = t.clone();
					return (
						jQuery(".inmap-input", _).each(function () {
							var s = jQuery(this),
								g = s.attr("name") + "[" + h + "]";
							s.attr("name", g),
								s.attr("placeholder", ""),
								s.get(0).nodeName != "SELECT" && s.val("");
						}),
						jQuery(this).before(_),
						inmap_setup_parameter_tooltips(),
						inmap_setup_colour_pickers(),
						!1
					);
				});
			e.append(l), e.sortable();
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
	var f = e.data("init_tab_key"),
		d = jQuery("select", e);
	d.hover(
		function () {
			jQuery(this).attr("size", jQuery("option", jQuery(this)).length);
		},
		function () {
			jQuery(this).removeAttr("size");
		},
	),
		d.change(function () {
			d.removeAttr("size");
			var n = jQuery(this).val();
			t.attr("class", "");
			var m = jQuery('input[name="_wp_http_referer"]', a),
				l = document.location.toString();
			l.indexOf("content=") > 0
				? (l = l.replace("content=" + f, "content=" + n))
				: (l = l + "&content=" + n),
				m.val(l),
				jQuery("." + n).first(),
				jQuery(".inmap-settings-tab").each(function () {
					var p = jQuery(this);
					p.hide(),
						n.indexOf("settings-tab") &&
							p.hasClass(n) &&
							(p.show(), t.addClass("inmap-active-" + n)),
						jQuery(".inmap-settings-section", p).each(function () {
							var o = jQuery(this);
							n.indexOf("settings-tab") > 0
								? o.show()
								: n.indexOf("settings-section") > 0 &&
									(o.hide(),
									o.hasClass(n) &&
										(p.show(), o.show(), t.addClass("inmap-active-" + n)));
						});
				});
		}),
		d.trigger("change");
};
window.inmap_admin_message = function (
	e = null,
	t = "info",
	a = "#inmap-admin-container .card",
) {
	if (e) {
		var f = jQuery(a).first();
		if (!f.length) var f = jQuery("#wpbody-content").first();
		if (f.length) {
			jQuery(".notice").each(function () {
				jQuery(this).remove();
			});
			var d = jQuery("<div />").attr({
					class: "inmap-notice notice notice-" + t,
				}),
				n = jQuery("<p />").html(e);
			d.append(n), f.prepend(d);
		} else alert(e);
	}
};
window.inmap_setup_colour_pickers = function () {
	jQuery(".inmap-colour-picker .inmap-input").wpColorPicker();
};
jQuery(document).ready(function () {
	inmap_setup_parameter_tooltips(),
		inmap_setup_accordions(),
		inmap_setup_settings_nav(),
		inmap_setup_repeatable_settings(),
		inmap_setup_dropdowns(),
		inmap_setup_colour_pickers();
});
