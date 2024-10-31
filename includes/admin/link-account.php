<?php

add_action('admin_post_prepear_login_user', 'prepear_login_user');
add_action('admin_post_prepear_logout_user', 'prepear_logout_user');

function prepear_admin_link_account()
{
    ?>

    <div class="wrap">
        <div class="card">
            <h1>Link your Prepear Pro account</h1>
            <div id="error-text"></div>
            <form action="<?php echo admin_url('admin-post.php') ?>" method="post">
                <?php wp_nonce_field('prepear_login_user') ?>
                <input type="hidden" name="action" value="prepear_login_user">
                <table class="form-table">
                    <tbody>
                        <tr id="email-form-group" class="form-field">
                            <th scope="row"><label for="email_input">Email*</label></th>
                            <td>
                                <input type="text" id="email_input" required name="email_input" class="regular-text">
                            </td>
                        </tr>
                        <tr id="password-form-group" class="form-field">
                            <th scope="row"><label for="password_input">Password*</label></th>
                            <td>
                                <input type="password" id="password_input" required name="password_input" class="regular-text">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit"><input type="submit" value="Login" class="button-primary" name="Submit"></p>
            </form>
        </div>
    </div>

    <?php
    prpr_render_errors();
}

function prepear_login_user()
{
    $errors = "";

    if (!wp_verify_nonce($_POST['_wpnonce'], 'prepear_login_user') || !current_user_can('administrator')) {
        return wp_redirect(admin_url('/options-general.php?page=prepear&error=EXPIRED'), 301);
    }

    if (!empty($_POST['email_input']) && !empty($_POST['password_input'])) {
        $email = $_POST['email_input'];
        $password = $_POST['password_input'];

        $response = wp_remote_post('https://app.prepear.com/api/v1/auth/local', array(
            'body' => array('email' => $email, 'password' => $password),
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";

            die();
        } else {
            if ($response['response']['code'] == 200) {
                $val = $response['cookies'][0]->value;
                update_option('prepear_auth', $val);
            } elseif ($response['response']['code'] == 400) {
                $body = json_decode($response['body'], true);
                $errors .= "&error=" . $body['message'];
            } else {
                $errors .= "&error=unknown";
            }
        }
    } else {
        $errors .= "&error=MISSING_INFO";
    }

    wp_redirect(admin_url('/options-general.php?page=prepear' . $errors), 301);
}

function prepear_logout_user()
{
    if (!wp_verify_nonce($_POST['_wpnonce'], 'prepear_logout_user') || !current_user_can('administrator')) {
        return wp_redirect(admin_url('/options-general.php?page=prepear&error=EXPIRED'), 301);
    }

    delete_option('prepear_auth');

    wp_redirect(admin_url('/options-general.php?page=prepear'), 301);
}

function prpr_render_errors()
{
    if (isset($_GET['error'])) {
        $script = "<script>jQuery(document).ready(function(){";

        switch ($_GET['error']) {
            case "EXPIRED":
                $script .= "
                    jQuery('#email-form-group').addClass('form-invalid');
                    jQuery('#error-text').html('<div class=\"error\">Link expired, please try again.</div>');
                ";
                break;
            case "FAIL_EMAIL":
                $script .= "
                    jQuery('#email-form-group').addClass('form-invalid');
                    jQuery('#error-text').html('<div class=\"error\">Invalid Email</div>');
                ";
                break;
            case "FAIL_PASSWORD":
                $script .= "
                    jQuery('#password-form-group').addClass('form-invalid');
                    jQuery('#error-text').html('<div class=\"error\">Invalid Password</div>');
                ";
                break;
            case "MISSING_INFO":
                $script .= "
                    jQuery('#error-text').html('<div class=\"error\">All fields required.</div>');
                ";
                break;
            default:
                $script .= "jQuery('#error-text').html('<div class=\"error\">Unknown Error. Please try again later.</div>');";
        }

        $script .= "})</script>";

        echo $script;
    }
}


?>