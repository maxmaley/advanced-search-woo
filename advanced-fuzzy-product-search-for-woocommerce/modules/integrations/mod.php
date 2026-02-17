<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class IntegrationsAfsw extends ModuleAfsw {
	public $currentTheme = array();
	public $themeReplaceField = 0;
	
	public $supportedThemes = array('storefront');
	public function init() {
		global $afswThemeReplaceField;
		$theme = $this->getCurrentTheme();
		if (!is_admin()) {
			$this->themeReplaceField = FrameAfsw::_()->getModule('fields')->getModel()->getThemeReplaceField();
			if (!empty($this->themeReplaceField)) {
				$afswThemeReplaceField = $this->themeReplaceField;
				$this->loadThemeCompatibility($theme['slug']);
			}
		}
	}
	
	public function isSupportedTheme( $theme ) {
		return in_array($theme, $this->supportedThemes);
	}
	
	public function isChildTheme() {
		return !empty($this->currentTheme['parent']);
	}
	
	public function getThemeReplaceField() {
		return !empty($this->currentTheme['parent']);
	}
	
	public function getCurrentTheme() {
		if (empty($this->currentTheme)) {
			$theme = wp_get_theme();

			if (is_object($theme) && is_a($theme, 'WP_Theme')) {
				$template = $theme->get_template();
				$stylesheet = $theme->get_stylesheet();
				$isChildTheme = $template !== $stylesheet;
				$themeSlug = sanitize_title($theme->Name);

				if ( $isChildTheme ) {
					$themeSlug = strtolower($template);
				}

				//$this->theme  = $theme;
				$themeName = $theme->name;
				$parentTheme = !empty($theme->parent_theme) ? $theme->parent_theme : '';
				$this->currentTheme = array('slug' => $themeSlug, 'name' => $themeName, 'parent' => $parentTheme);
			} else {
				$this->currentTheme = array('slug' => '', 'name' => '', 'parent' => '');
			}
			$this->currentTheme['support'] = $this->isSupportedTheme($this->currentTheme['slug']);
		}
		return $this->currentTheme;
	}
	public function loadThemeCompatibility( $slug ) {
		$file = $this->getModDir() . 'files/' . $slug . '.php';
		if (file_exists($file)) {
			require_once($file);
		}
	}
}
