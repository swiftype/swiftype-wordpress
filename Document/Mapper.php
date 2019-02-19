<?php

namespace Swiftype\SiteSearch\Wordpress\Document;

class Mapper
{
    public function convertToDocument($post)
    {
        $document = ['external_id' => $post->ID, 'fields' => $this->mapFields($post)];

        return apply_filters("swiftype_document_builder", $document, $post);
    }

    private function mapFields($post)
    {
        $fields = [];

        $fields[] = ['name' => 'object_type', 'type' => 'enum', 'value' => $post->post_type];
        $fields[] = ['name' => 'url', 'type' => 'enum', 'value' => $this->getPostUrl($post->ID)];
        $fields[] = ['name' => 'timestamp', 'type' => 'date', 'value' => $post->post_date_gmt];
        $fields[] = ['name' => 'title', 'type' => 'string', 'value' => $this->getSanitizedText($post, 'post_title')];
        $fields[] = ['name' => 'body', 'type' => 'text', 'value' => $this->getSanitizedText($post, 'post_content')];
        $fields[] = ['name' => 'excerpt', 'type' => 'text', 'value' => $this->getSanitizedText($post, 'post_excerpt')];


        $categories = wp_get_post_categories($post->ID);
        if (!empty($categories)) {
            $fields[] = ['name' => 'category', 'type' => 'enum', 'value' => $categories];
        }

        $tags = $this->getTags($post);
        if (!empty($tags)) {
            $fields[] = ['name' => 'tags', 'type' => 'enum', 'value' => $tags];
        }

        $imageUrl = $this->getImageUrl($post);
        if ($imageUrl) {
            $fields[] = ['name' => 'image', 'type' => 'enum', 'value' => $imageUrl];
        }

        $authorMeta = $this->getAuthorMeta($post);
        if (!empty($authorMeta)) {
            $fields[] = ['name' => 'author', 'type' => 'string', 'value' => $authorMeta];
        }

        return $fields;
    }

    private function getPostUrl($postId)
    {
        return get_permalink($postId);
    }

    private function getSanitizedText($post, $field)
    {
        $content = $post->$field;
        global $shortcode_tags;

        if (!empty($shortcode_tags) && is_array($shortcode_tags)) {
            # Replace the short code with its content (the 5th capture group) surrounded by spaces
            $pattern = get_shortcode_regex();
            $content = preg_replace("/$pattern/s", ' $5 ', $content);
        }

        return html_entity_decode(wp_strip_all_tags($content), ENT_QUOTES, "UTF-8");
    }

    private function getTags($post)
    {
        # TODO: post commit ?
        $tags = [];

        foreach (get_the_tags($post->ID) as $tag) {
            $tags[] = $tag->name;
        }

        return $tags;
    }

    private function getImageUrl($post)
    {
        $imageUrl = false;

        if ( current_theme_supports('post-thumbnails') && has_post_thumbnail($post->ID)) {
            // NOTE: returns false on failure
            $imageUrl = wp_get_attachment_url( get_post_thumbnail_id($post->ID ));
        }

        return $imageUrl;
    }

    private function getAuthorMeta($post)
    {
        $authorMeta = [];

        $nickname = trim(get_the_author_meta('nickname', $post->post_author));
        if (!empty($nickname)) {
            $authorMeta[] = $nickname;
        }

        $firstName = get_the_author_meta( 'first_name', $post->post_author );
        $lastName = get_the_author_meta( 'last_name', $post->post_author );
        $authorName = trim($firstName . " " . $lastName);

        if (!empty($authorName)) {
            $authorMeta[] = $authorName;
        }

        return $authorMeta;
    }
}
