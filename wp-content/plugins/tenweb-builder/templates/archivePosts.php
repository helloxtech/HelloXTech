<?php
namespace Tenweb_Builder;

include_once TWBB_DIR . '/templates/base.php';

class ArchivePostsTemplate extends BaseTemplate {

  public static function get_slug(){
    return "twbb_archive_posts";
  }

  public static function get_title(){
    return 'Archive Posts';
  }

  public static function print_twbb_template($template_id, $name = ""){
    if(\Elementor\Plugin::instance()->preview->is_preview_mode()) {
      //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo \Elementor\Plugin::instance()->preview->builder_wrapper('');
    } else {
      self::print_builder_content($template_id);
    }
  }

}
