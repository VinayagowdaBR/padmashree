<script>
(function($) {
    "use strict";

    appValidateForm($('#member-form'), {
        firstname: 'required',
        lastname: 'required',
        phone: 'required'
    });

})(jQuery);
</script>
