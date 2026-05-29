/**
 * Timetable preview + service deviations (admin phase 1).
 *
 * @package Museum_Railway_Timetable
 */
(function($) {
    'use strict';

    window.MRTAdminTimetablePreview = {
        init: function() {
            var $btn = $('#mrt-load-timetable-preview');
            if (!$btn.length) {
                return;
            }
            $btn.on('click', function() {
                var url = $btn.data('preview-url');
                var $wrap = $('#mrt-timetable-preview-frame-wrap');
                var $frame = $('#mrt-timetable-preview-frame');
                if (!url || !$wrap.length || !$frame.length) {
                    return;
                }
                if ($frame.attr('src') === 'about:blank') {
                    $frame.attr('src', url);
                }
                $wrap.removeClass('mrt-hidden').prop('hidden', false);
                $btn.prop('disabled', true).text(
                    window.MRTAdminUtils.msg('previewLoaded', 'Preview loaded')
                );
            });
        }
    };

})(jQuery);
