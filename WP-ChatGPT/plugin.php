<?php

/**
 * Plugin Name: ChatGPT评论机器人
 * Plugin URI: https://github.com/senge-dev/wordpress-chatgpt-plugin
 * Description: ChatGPT评论机器人，使用OpenAI的API回复用户的评论
 * Version: 1.0.0
 * Author: Senge Dev
 * Author URI: https://senge.dev
 */

// 为chatgpt_handle_comment函数添加hook，当用户评论时触发
add_action( 'comment_post', 'chatgpt_handle_comment' );


// 定义函数，当用户评论时触发
function chatgpt_handle_comment( $comment_id ) {
    // 获取评论内容
    $comment = get_comment( $comment_id );
    $comment_content = $comment->comment_content;
    // 判断评论是否为空
    if ( empty( $comment_content ) ) {
        return;
    }
    // 判断是否是回复，如果是回复则退出函数
    if ( $comment->comment_parent != 0 ) {
        return;
    }
    // 判断评论是否符合格式要求
    if ( strpos( $comment_content, "[GPT]" ) !== false ) {
        return;
    }
    if ( ! preg_match( '/^(\S+)!!(.*)/', $comment_content, $matches ) ) {
        return;
    }
    $api_key = $matches[1];
    $comment_text = $matches[2];
    // 保护用户的API Key，将前面的api_key和!!替换为[GPT]，并修改用户的评论
    $protected_comment_text = "[GPT]" . substr( $comment_content, strlen( $api_key ) + 2 );
    wp_update_comment( array(
        'comment_ID' => $comment_id,
        'comment_content' => $protected_comment_text,
    ) );
    // 使用ChatBot的身份回复用户
    $reply = "您的问题是：“" . $comment_text . "”我正在思考中...";
    $chatbot_user = get_user_by( 'login', 'ChatBot' );
    $chatbot_user_id = $chatbot_user->ID;
    $sys_prompt = "你是一个WordPress的评论机器人，当前文章名为“{$title}”，请勿回答和当前文章内容无关的问题。";
    // 调用OpenAI API获取回答
    $url = 'https://api.openai.com/v1/chat/completions';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
        json_encode(
            array(
                'messages' => array(
                    array(
                        'role' => 'system',
                        'content' => $sys_prompt
                    ),
                    array(
                        'role' => 'user',
                        'content' => $comment_text
                    )
                    ),
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 2048,
                'temperature' => 0.9,
                'top_p' => 1,
                'stream' => false,
                'n' => 1
            )
        )
    );
    curl_setopt($curl, CURLOPT_HEADERS, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Bearer '. $api_key,
        )
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    try{
        // 发送HTTP请求
        $response = curl_exec($curl);
    } catch (Exception $e) {
        // 异常处理
        $reply = "获取回复失败，如果您是本站的游客，请联系管理员，如果您是本站的管理员，请检测您的WordPress站点所在服务器的IP地址是否在OpenAI的支持区域。";
    } finally {
        // 无论是否发生异常，都要关闭curl
        curl_close($curl);
    }
    // 获取回复
    try{
        $body = json_decode( wp_remote_retrieve_body( $response ) );
        // 使用JavaScript运行：console.log($body)查看返回的数据（Debug）
        $reply = $body->choices[0]->message['content'];;
    } catch (Exception $e) {
        $reply = "获取回复失败，可能的原因是：API密钥错误、API密钥余额不足，或者OpenAI服务器出现故障。";
    } finally {
        echo '<script>';
        echo 'console.log("' . $body . '")';
        echo '</script>';
    }
    $commentdata = array(
        'comment_post_ID' => $post_id,
        'comment_author' => 'ChatBot',
        'comment_author_email' => 'chatbot@senge.dev',
        'comment_author_url' => 'https://senge.dev',
        'comment_content' => $reply,
        'comment_type' => '',
        'comment_parent' => $comment_id,
        'user_id' => $chatbot_user_id,
        'comment_author_IP' => ''
    );
    wp_insert_comment( $commentdata );
}
