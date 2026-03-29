<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Friendlink_Apply_Email {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_friendlink_application_submitted', array($this, 'send_submission_notification'), 10, 2);
        add_action('wp_friendlink_application_approved', array($this, 'send_approval_notification'), 10, 2);
        add_action('wp_friendlink_application_rejected', array($this, 'send_rejection_notification'));
    }
    
    public function send_submission_notification($application_id, $data) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $headers = $this->get_email_headers($site_name, $admin_email);
        
        $admin_sent = $this->send_email_to_admin($data, $headers);
        $this->send_email_to_applicant($data, $headers);
        
        return $admin_sent;
    }
    
    private function send_email_to_admin($data, $headers) {
        $subject = '【{blog_name}】收到来自 {site_name} 的友情链接申请';
        $template = '您收到一条新的友链申请';
        
        $subject = $this->parse_template($subject, $data);
        $message = $this->parse_template($template, $data);
        
        $content = $this->build_admin_notification_content($data);
        $html_message = $this->build_email_html($subject, $content, 'admin_notification');
        $html_headers = $this->get_html_email_headers($headers);
        
        $admin_email = get_option('admin_email');
        return wp_mail($admin_email, $subject, $html_message, $html_headers);
    }
    
    private function build_admin_notification_content($data) {
        $content = '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . __('尊敬的管理员，您好！', 'wp-friendlink-apply') . '</p>';
        $content .= '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . __('您收到一条新的友链申请，请及时审核。', 'wp-friendlink-apply') . '</p>';
        
        $content .= '<div style="background: #e7f3ff; border-left: 4px solid #4a90e2; border-radius: 8px; padding: 20px; margin: 20px 0;">';
        $content .= '<h3 style="margin: 0 0 15px 0; color: #1d2327; font-size: 16px;">' . __('申请详情', 'wp-friendlink-apply') . '</h3>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站标题：', 'wp-friendlink-apply') . '</strong>' . esc_html($data['site_name']) . '</p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站地址：', 'wp-friendlink-apply') . '</strong><a href="' . esc_url($data['site_url']) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($data['site_url']) . '</a></p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站描述：', 'wp-friendlink-apply') . '</strong>' . esc_html($data['site_description'] ?: __('无', 'wp-friendlink-apply')) . '</p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('申请人邮箱：', 'wp-friendlink-apply') . '</strong><a href="mailto:' . esc_attr($data['email']) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($data['email']) . '</a></p>';
        $content .= '</div>';
        
        $admin_url = admin_url('admin.php?page=wp-friendlink-apply');
        $content .= '<div style="text-align: center; margin: 25px 0;">';
        $content .= '<a href="' . esc_url($admin_url) . '" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">' . __('立即审核', 'wp-friendlink-apply') . '</a>';
        $content .= '</div>';
        
        return $content;
    }
    
    private function send_email_to_applicant($data, $headers) {
        $subject = '【{blog_name}】您的友情链接申请已提交';
        $template = '<p>您好！您的友链申请已成功提交到 {blog_name}。</p><h3>申请详情</h3><p><strong>网站标题：</strong>{site_name}</p><p><strong>网站地址：</strong>{site_url}</p><p>我们会在审核后尽快通知您结果。</p>';
        
        $subject = $this->parse_template($subject, $data);
        
        $content = $this->build_submission_confirmation_content($data);
        $html_message = $this->build_email_html($subject, $content, 'submission_confirmation');
        $html_headers = $this->get_html_email_headers($headers);
        
        return wp_mail($data['email'], $subject, $html_message, $html_headers);
    }
    
    private function build_submission_confirmation_content($data) {
        $blog_name = get_bloginfo('name');
        
        $content = '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . sprintf(__('您好！您的友链申请已成功提交到 %s。', 'wp-friendlink-apply'), esc_html($blog_name)) . '</p>';
        $content .= '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . __('我们会在审核后尽快通知您结果，请耐心等待。', 'wp-friendlink-apply') . '</p>';
        
        $content .= '<div style="background: #d4edda; border-left: 4px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;">';
        $content .= '<h3 style="margin: 0 0 15px 0; color: #155724; font-size: 16px;">' . __('申请详情', 'wp-friendlink-apply') . '</h3>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #155724;"><strong>' . __('网站标题：', 'wp-friendlink-apply') . '</strong>' . esc_html($data['site_name']) . '</p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #155724;"><strong>' . __('网站地址：', 'wp-friendlink-apply') . '</strong><a href="' . esc_url($data['site_url']) . '" style="color: #155724; text-decoration: underline;">' . esc_html($data['site_url']) . '</a></p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #155724;"><strong>' . __('网站描述：', 'wp-friendlink-apply') . '</strong>' . esc_html($data['site_description'] ?: __('无', 'wp-friendlink-apply')) . '</p>';
        $content .= '</div>';
        
        $content .= '<div style="background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 20px 0; text-align: center;">';
        $content .= '<p style="margin: 0; font-size: 14px; color: #6c757d;">⏳ ' . __('审核通常需要1-3个工作日', 'wp-friendlink-apply') . '</p>';
        $content .= '</div>';
        
        $content .= '<p style="margin: 20px 0 0 0; font-size: 16px; color: #28a745; font-weight: 600;">✨ ' . __('感谢您的申请！', 'wp-friendlink-apply') . '</p>';
        
        return $content;
    }
    
    private function get_email_headers($site_name, $admin_email) {
        return array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>'
        );
    }
    
    private function get_html_email_headers($plain_headers) {
        $site_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');
        
        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>',
            'MIME-Version: 1.0'
        );
    }
    
    public function send_approval_notification($application, $link_id) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        
        $subject = '【{blog_name}】恭喜！您的友情链接申请已通过';
        $template = '您的友情链接申请已审核';
        
        $data = array(
            'site_name' => $application->site_name,
            'site_url' => $application->site_url,
            'site_description' => $application->site_description,
            'email' => $application->email
        );
        
        $subject = $this->parse_template($subject, $data);
        
        $content = $this->build_approval_content($application, $site_name, $site_url);
        $html_message = $this->build_email_html($subject, $content, 'approval');
        $headers = $this->get_html_email_headers(array());
        
        return wp_mail($application->email, $subject, $html_message, $headers);
    }
    
    public function send_rejection_notification($application) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $reject_reason = isset($application->reject_reason) && !empty($application->reject_reason) 
            ? $application->reject_reason 
            : __('未提供具体原因', 'wp-friendlink-apply');
        
        $data = array(
            'site_name' => $application->site_name,
            'site_url' => $application->site_url,
            'site_description' => $application->site_description,
            'email' => $application->email,
            'reject_reason' => $reject_reason
        );
        
        $subject_template = '【{blog_name}】很遗憾，您的友情链接申请未通过';
        $template = '<p>您好！很遗憾地通知您，您的友情链接申请未通过审核。</p><h3>拒绝理由</h3><p style="background: #fff3cd; padding: 15px; border-radius: 8px; color: #856404;">{reject_reason}</p><h3>申请详情</h3><p><strong>网站标题：</strong>{site_name}</p><p><strong>网站地址：</strong>{site_url}</p><p>如有疑问，请联系我们。</p>';
        
        $subject = $this->parse_template($subject_template, $data);
        $content = $this->build_rejection_content($application, $reject_reason, $site_name, $admin_email);
        $html_message = $this->build_email_html($subject, $content, 'rejection');
        $headers = $this->get_html_email_headers(array());
        
        return wp_mail($application->email, $subject, $html_message, $headers);
    }
    
    private function build_approval_content($application, $site_name, $site_url) {
        $content = '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . __('您好！恭喜您的友情链接申请已通过审核。', 'wp-friendlink-apply') . '</p>';
        
        $content .= '<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">';
        $content .= '<h3 style="margin: 0 0 15px 0; color: #1d2327; font-size: 16px;">' . __('申请详情', 'wp-friendlink-apply') . '</h3>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站标题：', 'wp-friendlink-apply') . '</strong>' . esc_html($application->site_name) . '</p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站地址：', 'wp-friendlink-apply') . '</strong><a href="' . esc_url($application->site_url) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($application->site_url) . '</a></p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站描述：', 'wp-friendlink-apply') . '</strong>' . esc_html($application->site_description ?: __('无', 'wp-friendlink-apply')) . '</p>';
        $content .= '</div>';
        
        $content .= '<div style="background: #e7f3ff; border-left: 4px solid #4a90e2; padding: 15px; margin: 20px 0; border-radius: 4px;">';
        $content .= '<p style="margin: 0 0 10px 0; font-size: 14px; color: #1d2327; font-weight: 600;">' . __('请确保您的网站也添加了我们的友情链接：', 'wp-friendlink-apply') . '</p>';
        $content .= '<p style="margin: 5px 0; font-size: 14px; color: #495057;"><strong>' . __('网站名称：', 'wp-friendlink-apply') . '</strong>' . esc_html($site_name) . '</p>';
        $content .= '<p style="margin: 5px 0; font-size: 14px; color: #495057;"><strong>' . __('网站地址：', 'wp-friendlink-apply') . '</strong><a href="' . esc_url($site_url) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($site_url) . '</a></p>';
        $content .= '</div>';
        
        $content .= '<p style="margin: 20px 0 0 0; font-size: 16px; color: #28a745; font-weight: 600;">✨ ' . __('感谢您的支持！', 'wp-friendlink-apply') . '</p>';
        
        return $content;
    }
    
    private function build_rejection_content($application, $reject_reason, $site_name, $admin_email) {
        $content = '<p style="margin: 0 0 15px 0; font-size: 16px; line-height: 1.6;">' . __('您好！很遗憾地通知您，您的友情链接申请未通过审核。', 'wp-friendlink-apply') . '</p>';
        
        $content .= '<div style="background: #fff3cd; border: 2px dashed #ffc107; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center;">';
        $content .= '<p style="margin: 0 0 10px 0; font-size: 14px; color: #856404;">' . __('拒绝理由', 'wp-friendlink-apply') . '</p>';
        $content .= '<p style="margin: 0; font-size: 18px; color: #856404; font-weight: 600; line-height: 1.5;">' . nl2br(esc_html($reject_reason)) . '</p>';
        $content .= '</div>';
        
        $content .= '<div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0;">';
        $content .= '<h3 style="margin: 0 0 15px 0; color: #1d2327; font-size: 16px;">' . __('申请详情', 'wp-friendlink-apply') . '</h3>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站标题：', 'wp-friendlink-apply') . '</strong>' . esc_html($application->site_name) . '</p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站地址：', 'wp-friendlink-apply') . '</strong><a href="' . esc_url($application->site_url) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($application->site_url) . '</a></p>';
        $content .= '<p style="margin: 8px 0; font-size: 14px; color: #495057;"><strong>' . __('网站描述：', 'wp-friendlink-apply') . '</strong>' . esc_html($application->site_description ?: __('无', 'wp-friendlink-apply')) . '</p>';
        $content .= '</div>';
        
        $content .= '<p style="margin: 20px 0 0 0; font-size: 14px; color: #6c757d;">' . __('如有疑问，请联系我们：', 'wp-friendlink-apply') . '<a href="mailto:' . esc_attr($admin_email) . '" style="color: #4a90e2; text-decoration: none;">' . esc_html($admin_email) . '</a></p>';
        
        return $content;
    }
    
    private function build_email_html($title, $content, $type = 'default') {
        $blog_name = get_bloginfo('name');
        $blog_url = home_url();
        $current_time = current_time('Y-m-d H:i:s');
        
        $header_colors = array(
            'admin_notification' => 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);',
            'submission_confirmation' => 'background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);',
            'approval' => 'background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);',
            'rejection' => 'background: linear-gradient(135deg, #ff8a8a 0%, #ff6b6b 100%);',
            'default' => 'background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);'
        );
        
        $header_style = isset($header_colors[$type]) ? $header_colors[$type] : $header_colors['default'];
        
        $html = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html($title) . '</title>
    <style>
        .email-content h3 {
            color: #1d2327;
            font-size: 16px;
            font-weight: 600;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .email-content p {
            margin: 12px 0;
            line-height: 1.8;
        }
        .email-content strong {
            color: #495057;
        }
        .email-content a {
            color: #4a90e2;
            text-decoration: none;
        }
        .email-content a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="' . $header_style . ' padding: 30px 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">' . esc_html($blog_name) . '</h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">' . esc_html($title) . '</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px; color: #333; font-size: 15px; line-height: 1.8;">
                            <div class="email-content" style="color: #333; font-size: 15px; line-height: 1.8;">
                                ' . $content . '
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background: #f8f9fa; padding: 30px 40px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #6c757d;">
                                <strong>' . __('发送时间：', 'wp-friendlink-apply') . '</strong>' . esc_html($current_time) . '
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #adb5bd;">
                                ' . __('此邮件由系统自动发送，请勿回复。', 'wp-friendlink-apply') . '
                            </p>
                            <p style="margin: 15px 0 0 0;">
                                <a href="' . esc_url($blog_url) . '" style="color: #4a90e2; text-decoration: none; font-size: 14px;">' . esc_html($blog_name) . '</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        return $html;
    }
    
    private function parse_template($template, $data) {
        $replacements = array(
            '{site_name}' => $data['site_name'] ?? '',
            '{site_url}' => $data['site_url'] ?? '',
            '{site_description}' => $data['site_description'] ?? '',
            '{email}' => $data['email'] ?? '',
            '{blog_name}' => get_bloginfo('name'),
            '{blog_url}' => home_url(),
            '{reject_reason}' => $data['reject_reason'] ?? '',
            '{admin_email}' => get_option('admin_email')
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
