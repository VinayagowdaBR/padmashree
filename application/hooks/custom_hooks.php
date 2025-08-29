<?php
function inject_affiliate_users_dropdown() {
    $CI =& get_instance();

    if ($CI->uri->segment(1) === 'leads' && in_array($CI->uri->segment(2), ['lead', 'edit_lead'])) {
        $CI->db->select('username');
        $CI->db->from('tblaffiliate_users');
        $users = $CI->db->get()->result_array();

        // Start JavaScript output
        echo '<script>
        $(document).ready(function() {
            var $affiliateField = $("select[name^=\'custom_fields\'][name$=\'[Affiliate]\']");

            if ($affiliateField.length) {
                $affiliateField.empty();
                $affiliateField.append($("<option>", { value: "", text: "Select Affiliate" }));';

        // Inject each user as option
        foreach ($users as $user) {
            $username = htmlspecialchars($user['username'], ENT_QUOTES);
            echo '$affiliateField.append($("<option>", { value: "' . $username . '", text: "' . $username . '" }));';
        }

        // Close JS
        echo '
            }
        });
        </script>';
    }
}
?>
