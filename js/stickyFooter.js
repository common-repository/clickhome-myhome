jQuery(function ($) {
    /* StickyFooter */
    var $window = $(window);
    var $stickies = $('.mh-sticky-footer').each(function () {
        var $this = $(this);
        var origBottomPadding;

        $this.checkSticky = function () {
            $this.$parent = $this.closest('.mh-products .mh-card'); // console.log('chkSticky', $this.$parent);
            $this.params = {
                topLimit: ($this.$parent.offset().top) + $this.outerHeight() + 100,
                btmLimit: ($this.$parent.offset().top + $this.$parent.outerHeight())
            };

            if(!origBottomPadding) origBottomPadding = parseInt($this.$parent.css('padding-bottom'));
            $this.$parent.css('padding-bottom', origBottomPadding + $this.height());

            if (($window.scrollTop() + $window.height()) >= $this.params.topLimit &&
                ($window.scrollTop() + $window.height()) <= $this.params.btmLimit) {
                $this.addClass('is-fixed').css({
                    'opacity': 1,
                    'left': $this.$parent.offset().left + 1,
                    'width': $this.$parent.outerWidth() - 2
                });//.text('within parent: fixed');
            } else {
                $this.removeClass('is-fixed').attr('style', '');//.text('viewport outside parent: absolute');

                if (($window.scrollTop() + $window.height()) <= $this.params.topLimit)
                    $this.css('opacity', 0);
            }
        }
        // check the sticky element on page load
        $this.checkSticky();

        // check the sticky element on scrolling
        $window.bind('content-loaded scroll resize', function () {
            $this.checkSticky();
        });
    });
});