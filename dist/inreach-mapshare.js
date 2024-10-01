window.inmap_maps=[];window.inmap_create_map=function(e=null,t=null,n=null){if(!e||!t||!jQuery||typeof inmap_L!="object")return!1;var f={attributionControl:!1},m="inmap-"+e,r=jQuery("#"+m);if(!r.length)return!1;var u=inmap_L.map(m,f),d=inmap_L.control.attribution({prefix:!1});d.addAttribution('<a href="https://github.com/OpenGIS/inreach-mapshare">Inreach Mapshare</a>'),d.addTo(u),r.data("map_l",u),inmap_maps[e]=u;var c=jQuery("body").first(),o=r.parents(".inmap-wrap",c),y=jQuery(".inmap-info",o),v={},_={},s={},w={},x={},k={},Q={},N=function(){var a=y.height(),l=w.height(),p=a-l;p>0?y.css("padding-top",l+"px"):y.removeAttr("style")},F=function(){E(),D(),jQuery(window).on("resize",function(){g()}),g()},g=function(){o.hasClass("inmap-fullscreen")?o.css({width:c.width()+"px",height:c.height()+"px",zIndex:9999999999999}):o.removeAttr("style"),N(),typeof x.get=="function"&&x.get(0).scrollIntoView({behaviour:"smooth",block:"center"}),u.invalidateSize(),Q&&Q.getLatLng&&u.setView(Q.getLatLng())},D=function(){k=jQuery(".leaflet-control-container .leaflet-top",r).first();var a=jQuery("<div />").attr({class:"inmap-control leaflet-bar leaflet-control"}).append(jQuery("<a />").attr({class:"inmap-button inmap-icon inmap-icon-fullscreen",href:"#",title:"Fullscreen",role:"button","aria-label":"Fullscreen"}).on("click",function(p){p.preventDefault(),c.toggleClass("inmap-has-single"),o.toggleClass("inmap-fullscreen"),g()}));k.addClass("inmap-map-ui").append(a);var l=jQuery("<div />").attr({class:"inmap-control leaflet-bar leaflet-control"}).append(jQuery("<a />").attr({class:"inmap-button inmap-icon inmap-icon-detail",href:"#",title:"Details",role:"button","aria-label":"Details"}).on("click",function(p){p.preventDefault(),o.toggleClass("inmap-info-hidden"),g()}));k.append(l)},E=function(){for(id in s){var a=jQuery(".inmap-info-title",s[id]),l=a.text().replace("[","<span>").replace("]","</span>");a.html(l),s[id].hasClass("inmap-only")||s[id].addClass("inmap-hide-extended");var p=jQuery(".inmap-icon",_[id]);s[id].append(p.clone()),jQuery("table tr",s[id]).each(function(){var h=jQuery(this),L=jQuery("td",h);jQuery("th",h).addClass("inmap-info-extended");var V=h.attr("class").replace("inmap-assoc_array-",""),z=L.text();switch(V){case"elevation":var S=parseFloat(z);isNaN(S)||L.text(S.toFixed(1)+" (m) "+(S*3.28084).toFixed(1)+" (ft) ");break;case"velocity":var T=parseFloat(z);isNaN(T)||L.text(T.toFixed(1)+" (km/h) "+(T/1.609344).toFixed(1)+" (mph) ");break;case"time_utc":case"valid_gps_fix":h.addClass("inmap-info-extended");break}}),s[id].hasClass("inmap-last")&&(w=s[id])}inmap_shortcode_js.detail_expanded==="false"&&o.addClass("inmap-info-hidden")},b=function(a=null,l="active",p=!1){var h=14;for(this_id in v)this_id===a?(l=="active"&&s[this_id].hasClass("inmap-active")?(s[this_id].removeClass("inmap-hide-extended"),u.getZoom()<h?u.setView(v[this_id].getLatLng(),h):u.setView(v[this_id].getLatLng())):(_[this_id].addClass("inmap-"+l),s[this_id].addClass("inmap-"+l)),l=="active"&&(o.removeClass("inmap-info-hidden"),p&&s[this_id].get(0).scrollIntoView({behaviour:"smooth",block:"center"}),u.setView(v[this_id].getLatLng()))):l=="active"?(s[this_id].removeClass("inmap-active"),s[this_id].hasClass("inmap-last")||_[this_id].removeClass("inmap-active")):(s[this_id].removeClass("inmap-"+l),_[this_id].removeClass("inmap-"+l));l=="active"?(a&&(Q=v[a]),N()):Q={},g()},I=function(){if(!n||!JSON.stringify(n))return!1;var a={weight:3,opacity:.5};typeof inmap_shortcode_js.route_colour=="string"&&inmap_shortcode_js.route_colour.length&&(a.color=inmap_shortcode_js.route_colour);var l=inmap_L.geoJSON(n,{style:a,pointToLayer:function(p,h){},onEachFeature:function(p,h){h.bindTooltip("Planned Route")}}).addTo(u);u.fitBounds(C.getBounds().extend(l.getBounds()))},O="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";if(typeof inmap_shortcode_js.basemap_url=="string"&&inmap_shortcode_js.basemap_url.length)var O=inmap_shortcode_js.basemap_url;var q='&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>';if(typeof inmap_shortcode_js.basemap_attribution=="string"&&inmap_shortcode_js.basemap_attribution.length)var q=inmap_shortcode_js.basemap_attribution;inmap_L.tileLayer(O,{maxZoom:19,attribution:q}).addTo(u);var C=inmap_L.geoJSON(t,{style:function(a){if(typeof a.properties.style=="object")return a.properties.style},pointToLayer:function(a,l){if(typeof a.properties.id>"u")return!1;var p=a.properties.id.toString();return typeof a.properties.icon=="object"?v[p]=inmap_L.marker(l,{icon:inmap_L.divIcon(a.properties.icon)}):v[p]=inmap_L.marker(l),s[p]=jQuery("<div />").addClass(a.properties.className).attr("title",a.properties.title).html(a.properties.description).hover(function(){b(p,"hover")},function(){b(null,"hover")}).on("click",function(){x=jQuery(this),b(p,"active")}),y.append(s[p]),v[p]},onEachFeature:function(a,l){if(typeof a.properties.id>"u")return!1;var p=a.properties.id.toString();l.on("add",function(h){_[p]=jQuery(h.target.getElement()).attr("title",a.properties.title).addClass(a.properties.className).data("marker_l",h.target).on("mouseenter",function(){b(p,"hover",!0)}).on("click",function(){b(p,"active",!0)})})}});C.addTo(u),C.on("add",function(){F()}),u.fitBounds(C.getBounds()),I()};window.inmap_setup_parameter_tooltips=function(){jQuery("a.inmap-tooltip").on({mouseenter:function(e){var t=jQuery(this).data("title");jQuery('<p id="inmap-tooltip-active"></p>').text(t).appendTo("body").fadeIn("slow")},mouseleave:function(e){jQuery("#inmap-tooltip-active").remove()},mousemove:function(e){if(inmap_is_touch_device())var t=e.pageX-250;else var t=e.pageX-220;var n=e.pageY+5;jQuery("#inmap-tooltip-active").css({top:n,left:t})}})};window.inmap_is_touch_device=function(){var e=" -webkit- -moz- -o- -ms- ".split(" "),t=function(f){return window.matchMedia(f).matches};if("ontouchstart"in window||window.DocumentTouch&&document instanceof DocumentTouch)return!0;var n=["(",e.join("touch-enabled),("),"heartz",")"].join("");return t(n)};window.inmap_setup_accordions=function(){var e=jQuery(".inmap-accordion-container");e.length&&(e.addClass("inmap-self-clear"),e.each(function(){var t=0;jQuery(".inmap-accordion-group",jQuery(this)).each(function(){var n=jQuery(this);n.addClass("inmap-self-clear"),n.data("inmap-index",t);var f=jQuery(".inmap-accordion-group-content",n);t==0?(n.addClass("inmap-first inmap-active"),f.show().addClass(t)):f.hide().addClass(t),jQuery("legend",jQuery(this)).each(function(){var m=jQuery(this).html();if(m.indexOf("[+]")==-1&&m.indexOf("[-]")==-1){var r=t==0?"[-]":"[+]";jQuery(this).html(m+" <span>"+r+"</span>")}jQuery(this).click(function(){var u=jQuery(this).parents(".inmap-accordion-group").data("inmap-index");jQuery(".inmap-accordion-group",jQuery(this).parents(".inmap-accordion-container")).each(function(){if(jQuery(this).data("inmap-index")==u){var d=jQuery("legend",jQuery(this));jQuery(this).hasClass("inmap-active")?(d.html(d.html().replace("[-]","[+]")),jQuery(this).removeClass("inmap-active"),jQuery(".inmap-accordion-group-content",jQuery(this)).slideUp()):(d.html(d.html().replace("[+]","[-]")),jQuery(this).addClass("inmap-active"),jQuery(".inmap-accordion-group-content",jQuery(this)).slideDown())}else{jQuery(this).removeClass("inmap-active");var d=jQuery("legend",jQuery(this));d.html(d.html().replace("[-]","[+]")),jQuery(".inmap-accordion-group-content",jQuery(this)).slideUp()}})})}),t++})}))};window.inmap_setup_repeatable_settings=function(){jQuery(".inmap-settings-tab .inmap-repeatable").each(function(){var e=jQuery(this);jQuery(".form-table",e).each(function(){var t=jQuery(this),n=[];for(t.remove(),jQuery(".inmap-input",t).each(function(){var c=jQuery(this);if(c.addClass("inmap-"+c.data("id")),c.get(0).nodeName!="SELECT")var o=c.val();else var o=c.data("multi-value");typeof o!="string"&&(o=o.toString()),o=o.split(inmap_admin_js.multi_value_seperator);for(i in o)typeof n[i]!="object"&&(n[i]={}),n[i][c.data("id")]=o[i]}),i=0;i<n.length;i++){var f=t.clone();for(j in n[i]){var m=n[i][j],r=jQuery(".inmap-input-"+j,f);r.attr("name",r.attr("name")+"["+i+"]"),r.get(0).nodeName=="SELECT"&&!jQuery("option[value='"+n[i][j]+"']",r).length&&(m=jQuery("option",r).first().val()),r.attr("value",m).val(m),r.parents(".inmap-control-group").hasClass("inmap-uneditable")&&r.attr("readonly","readonly")}var u=jQuery("<div />").text("x").attr("title",inmap_admin_js.lang.repeatable_delete_title).addClass("inmap-delete").on("click",function(c){c.preventDefault();var o=jQuery(this).parents(".form-table");return o.remove(),!1});f.append(u),e.append(f),e.attr("data-count",i),inmap_setup_parameter_tooltips()}var d=jQuery("<button />").html('<i class="ion ion-plus"></i>').addClass("button inmap-add").on("click",function(c){c.preventDefault();var o=jQuery(this).parents(".inmap-repeatable"),y=parseInt(o.attr("data-count")),v=y+1;o.attr("data-count",v);var _=t.clone();return jQuery(".inmap-input",_).each(function(){var s=jQuery(this),w=s.attr("name")+"["+v+"]";s.attr("name",w),s.attr("placeholder",""),s.get(0).nodeName!="SELECT"&&s.val("")}),jQuery(this).before(_),inmap_setup_parameter_tooltips(),inmap_setup_colour_pickers(),!1});e.append(d),e.sortable()})})};window.inmap_setup_dropdowns=function(){jQuery(".inmap-parameters-container").each(function(){var e=jQuery(this);jQuery("select",e).each(function(){var t="inmap-dropdown-"+jQuery(this).data("id")+"-";t+=jQuery(this).val(),e.addClass(t),jQuery(this).on("change",function(){var n="inmap-dropdown-"+jQuery(this).data("id")+"-";jQuery("option",jQuery(this)).each(function(){e.removeClass(n+jQuery(this).attr("value"))}),n+=jQuery(this).val(),e.addClass(n)})})})};window.inmap_setup_settings_nav=function(){var e=jQuery("body.wp-admin #inmap-settings-nav");if(!e)return!1;var t=jQuery("#inmap-admin-container"),n=jQuery("form",t);jQuery(".inmap-settings-tab",t);var f=e.data("init_tab_key"),m=jQuery("select",e);m.hover(function(){jQuery(this).attr("size",jQuery("option",jQuery(this)).length)},function(){jQuery(this).removeAttr("size")}),m.change(function(){m.removeAttr("size");var r=jQuery(this).val();t.attr("class","");var u=jQuery('input[name="_wp_http_referer"]',n),d=document.location.toString();d.indexOf("content=")>0?d=d.replace("content="+f,"content="+r):d=d+"&content="+r,u.val(d),jQuery("."+r).first(),jQuery(".inmap-settings-tab").each(function(){var c=jQuery(this);c.hide(),r.indexOf("settings-tab")&&c.hasClass(r)&&(c.show(),t.addClass("inmap-active-"+r)),jQuery(".inmap-settings-section",c).each(function(){var o=jQuery(this);r.indexOf("settings-tab")>0?o.show():r.indexOf("settings-section")>0&&(o.hide(),o.hasClass(r)&&(c.show(),o.show(),t.addClass("inmap-active-"+r)))})})}),m.trigger("change")};window.inmap_admin_message=function(e=null,t="info",n="#inmap-admin-container .card"){if(e){var f=jQuery(n).first();if(!f.length)var f=jQuery("#wpbody-content").first();if(f.length){jQuery(".notice").each(function(){jQuery(this).remove()});var m=jQuery("<div />").attr({class:"inmap-notice notice notice-"+t}),r=jQuery("<p />").html(e);m.append(r),f.prepend(m)}else alert(e)}};window.inmap_setup_colour_pickers=function(){jQuery(".inmap-colour-picker .inmap-input").wpColorPicker()};jQuery(document).ready(function(){inmap_setup_parameter_tooltips(),inmap_setup_accordions(),inmap_setup_settings_nav(),inmap_setup_repeatable_settings(),inmap_setup_dropdowns(),inmap_setup_colour_pickers()});