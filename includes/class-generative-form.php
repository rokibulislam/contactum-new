<?php


class GenerativeForm {

    public function test() {

        $api_key = 'AIzaSyDv3LxbRZrDOnPRMrKDsASqgDsJiN4V3A4';

        $prompt = "
        You are a form schema generator.
        Return ONLY valid JSON.
        No markdown. No explanation.

        Output format:
        {
        \"form_fields\": [
            {
            \"name\": \"\",
            \"label\": \"\",
            \"type\": \"\",
            \"placeholder\": \"\",
            \"required\": true
            }
        ]
        }

        Form description: Custom Feedback Form
        ";

        $body = [
        'contents' => [
            [
            'parts' => [
                ['text' => $prompt]
            ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0,
            'topP' => 1,
            'maxOutputTokens' => 500
        ]
        ];

        $response = wp_remote_post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key,
        [
            'headers' => [
            'Content-Type' => 'application/json'
            ],
            'body'    => json_encode($body),
            'timeout' => 60
        ]
        );

        $result = json_decode(wp_remote_retrieve_body($response), true);
        $json   = $result['candidates'][0]['content']['parts'][0]['text'];

        print_r($result);

                exit;
    }
}