function screenSize() { var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
        h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight; return retorno = { largura: w, altura: h }, retorno }

function margin(w, h) { jQuery(".videoBG").height(h).width(w), jQuery(".videoBG_wrapper").height(h).width(w) }

function video(element) { elClass = jQuery(element).attr("class"), regex = new RegExp("post_[0-9]+"), classFound = regex.exec(elClass), url = videoURL[classFound[0]], jQuery(element).videoBG({ autoplay: !0, loop: !0, scale: !0, zIndex: 0, opacity: 1, textReplacement: !1, width: "100%", height: "100%", ogv: url, webm: url, mp4: url, poster: url }) }

function recarregar() { tela = screenSize(), jQuery(".lpccp-background-media").each(function(index) { video(jQuery(this)) }) }
jQuery(document).ready(function() { recarregar(), jQuery(".lpccp-menu-navegation").length > 0 && (color = jQuery("section").eq(0).filter(".lpccp-column").css(["backgroundColor", "color"]), jQuery(".lpccp-menu-navegation").css({ backgroundColor: color.backgroundColor, borderBottomColor: color.color, color: color.color })) });