<?php
/**
 * Email Footer
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.0.0
 *
 * @var WC_Email $email
 */

defined('ABSPATH') || exit;

$email_footer_text = apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_footer">
                                    <tr>
                                        <td valign="top">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td id="credit" valign="top">
                                                        <?php echo wp_kses_post(wpautop(wptexturize($email_footer_text))); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
