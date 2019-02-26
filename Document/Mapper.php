<?php

namespace Swiftype\SiteSearch\Wordpress\Document;

/**
 * Provides a consistent way to transform a post into a searchable document.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Mapper
{
    /**
     * Convert the post into an indexable document.
     *
     * @param object $post
     *
     * @return array
     */
    public function convertToDocument($post)
    {
        $document = ['external_id' => $post->ID, 'fields' => $this->mapFields($post)];

        return apply_filters("swiftype_document_builder", $document, $post);
    }

    /**
     * Map post data into document fields.
     *
     * @param object $post
     *
     * @return array
     */
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
            $categories = array_map('\strval', $categories);
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

    /**
     * Return the post URL.
     *
     * @param int $postId
     *
     * @return string|false
     */
    private function getPostUrl($postId)
    {
        return get_permalink($postId);
    }

    /**
     * Retrieve text value from a post field and prepare it to be indexed.
     *
     * @param object $post
     * @param string $field
     *
     * @return string
     */
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

    /**
     * Retrieve tags from a post.
     *
     * @param object $post
     *
     * @return string[]
     */
    private function getTags($post)
    {
        # TODO: post commit ?
        $tags = [];
        $tagObjects = get_the_tags($post->ID);
        if ($tagObjects) {
            foreach ($tagObjects as $tag) {
                $tags[] = $tag->name;
            }
        }

        return $tags;
    }

    /**
     * Retrieve image URL from a post.
     *
     * @param object $post
     *
     * @return string|false
     */
    private function getImageUrl($post)
    {
        $imageUrl = false;

        if (current_theme_supports('post-thumbnails') && has_post_thumbnail($post->ID)) {
            // NOTE: returns false on failure
            $imageUrl = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
        }

        return $imageUrl;
    }

    /**
     * Retrieve author meta from a post.
     *
     * @param object $post
     *
     * @return string[]
     */
    private function getAuthorMeta($post)
    {
        $authorMeta = [];

        $nickname = trim(get_the_author_meta('nickname', $post->post_author));
        if (!empty($nickname)) {
            $authorMeta[] = $nickname;
        }

        $firstName = get_the_author_meta('first_name', $post->post_author);
        $lastName = get_the_author_meta('last_name', $post->post_author);
        $authorName = trim($firstName . " " . $lastName);

        if (!empty($authorName)) {
            $authorMeta[] = $authorName;
        }

        return $authorMeta;
    }
}
