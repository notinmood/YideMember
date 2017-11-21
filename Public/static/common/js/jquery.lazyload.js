(function ($) {
    $.fn.lazyload = function (options) {
        var settings = {
            threshold: 0,
            failurelimit: 0,
            event: "scroll",
            effect: "show",
            attr: "_src",
            container: window
        };
        if (options) {
            $.extend(settings, options);
        }
        var elements = this;
        if ("scroll" == settings.event) {
            $(settings.container).bind("scroll",
			function (event) {
			    var counter = 0;
			    elements.each(function () {
			        if (!$.belowthefold(this, settings) && !$.rightoffold(this, settings)) {
			            $(this).trigger("appear");
			        } else {
			            if (counter++ > settings.failurelimit) {
			                return false;
			            }
			        }
			    });
			    var temp = $.grep(elements,
				function (element) {
				    return !element.loaded;
				});
			    elements = $(temp);
			});
        }
        return this.each(function () {
            var self = this;
            if ("scroll" != settings.event || $.belowthefold(self, settings) || $.rightoffold(self, settings)) {
                if (settings.placeholder) {
                    //  $(self).attr("src", settings.placeholder)
                } else {
                    //                  //  $(self).removeAttr("src")
                }
            } else {
                $(self).attr("src", $(self).attr("_src"));
                self.loaded = true;
            }
            $(self).one("appear",
			function () {
			    if (!this.loaded) {
			        $("<img />").bind("load",
					function () {
					    $(self).hide().attr("src", $(self).attr("_src"))[settings.effect](settings.effectspeed);
					    self.loaded = true;
					}).attr("src", $(self).attr("_src"));
			    }
			});
            if ("scroll" != settings.event) {
                $(self).bind(settings.event,
				function (event) {
				    if (!self.loaded) {
				        $(self).trigger("appear");
				    }
				});
            }
        });
    };
    var b = true;
    $.belowthefold = function (element, settings) {
        var $top;
        var $height;
        if (settings.container === undefined || settings.container === window) {
            $top = $(window).scrollTop();
            $height = $(window).height();
        } else {
            $top = $(settings.container).offset().top;
            $height = $(settings.container).height();
        }
        var fold = $top + $height;
        var $ETop = $(element).offset().top;
        //return $ETop <= fold && $ETop >= $top;

        return ($ETop >= fold - settings.threshold);
    };
    $.rightoffold = function (element, settings) {
        if (settings.container === undefined || settings.container === window) {
            var fold = $(window).width() + $(window).scrollLeft();
        } else {
            var fold = $(settings.container).offset().left + $(settings.container).width();
        }
        return fold <= $(element).offset().left - settings.threshold;
    };
    $.extend($.expr[':'], {
        "below-the-fold": "$.belowthefold(a, {threshold : 0, container: window})",
        "above-the-fold": "!$.belowthefold(a, {threshold : 0, container: window})",
        "right-of-fold": "$.rightoffold(a, {threshold : 0, container: window})",
        "left-of-fold": "!$.rightoffold(a, {threshold : 0, container: window})"
    });
})(jQuery);

