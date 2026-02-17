<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 
$options = UtilsAfsw::getArrayValue($this->settings, 'search', array(), 2);
$module = $this->getModule();
$modPath = $module->getModPath();
$fuzzyModes = $module->getFuzzyModes();
$searchModes = $module->getSearchModes();
$searchLogic = array('or' => __('or', 'advanced-fuzzy-search'), 'and' => __('and', 'advanced-fuzzy-search'));
$scopes = $module->getSearchScopes();
$orderList = UtilsAfsw::getArrayValue($options, 'order');
$orderArr = explode(',', $orderList);
$stockValues = array(
	'instock' => esc_html__('In Stock', 'advanced-fuzzy-search'),
	'outofstock' => esc_html__('Out of Stock', 'advanced-fuzzy-search'),
	'onbackorder' => esc_html__('On Backorder', 'advanced-fuzzy-search'),
);
$sortValues = array(
	'' => esc_html__('default', 'advanced-fuzzy-search'),
	'relevant' => esc_html__('In order of scope placement', 'advanced-fuzzy-search'),
	'title' => esc_html__('By title', 'advanced-fuzzy-search'),
);
$forVariable = array(
	'' => esc_html__('Not search by variations', 'advanced-fuzzy-search'),
	'var' => esc_html__('Search only by variations parameters', 'advanced-fuzzy-search'),
	'all' => esc_html__('Search by variations and parent product parameters', 'advanced-fuzzy-search'),
);
?>
<div id="afswSearchOptions">
<?php
HtmlAfsw::hidden('search[order]', array('value' => $orderList, 'attrs' => 'id="afswScopesOrder"'));
?>
	<div class="afsw-scroll-wrapper">
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Products search scopes', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-sections-list" id="afswScopesList">
<?php 
foreach ($scopes as $scope => $d) {
	if (!in_array($scope, $orderArr)) {
		$orderArr[] = $scope;
	}
}
foreach ($orderArr as $scope) {
	if (!isset($scopes[$scope])) {
		continue;
	}
	$sc = $scopes[$scope];
	$withOptions = !isset($sc['options']) || ( false !== $sc['options'] );
	$proScope = $sc['pro'];
	?>
				<div class="afsw-section" data-type="<?php echo esc_attr($scope); ?>">
					<div class="afsw-section-header">
						<div class="options-value">
							<?php 
								HtmlAfsw::checkboxToggle('search[by_' . $scope . ']', array(
									'checked' => UtilsAfsw::getArrayValue($options, 'by_' . $scope, empty($sc['default']) ? 0 : $sc['default'], 1, false, true),
									'attrs' => $proScope && !$this->is_pro ? 'disabled' : ''
							));
							?>
						</div>
						<div class="afsw-section-title"><?php echo esc_html($sc['name']); ?></div>
						<?php 
						if ($proScope && !$this->is_pro) {
							HtmlAfsw::proOptionLink();
						} else if ($withOptions) { 
							?>
						<a href="#" class="afsw-section-toggle"><i class="fa fa-chevron-down"></i></a>
						<?php } ?>
					</div>
					<?php 
					if ($withOptions) { 
						if ($proScope) {
							DispatcherAfsw::doAction('fieldsIncludeTpl', 'searchScope' . strFirstUpAfsw($scope), array('options' => $options, 'modes' => $searchModes, 'logics' => $searchLogic, 'variables' => $forVariable));
						} else {
							include_once 'searchScope' . strFirstUpAfsw($scope) . '.php';
						}
					}
					?>
				</div>
	<?php } ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Base config', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Note that for parameters with long texts (description, short description) instead of fuzzy method mysql FULL-TEXT indexing and search methods are used.', 'advanced-fuzzy-search'); ?>">
				<?php 
					esc_html_e('Fuzzy mode', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('search[fuzzy_mode]', array(
							'options' => $fuzzyModes,
							'value' => UtilsAfsw::getArrayValue($options, 'fuzzy_mode', 'normal', 0, false, true),
						));
						?>
				</div>
				<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabSearchFuzzy', array('options' => $options)); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Minimum characters', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Minimum characters', 'advanced-fuzzy-search'); ?>
			</div>			
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::number('search[min_chars]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'min_chars', '3', 1),
							'attrs' => 'min="1" class="woobewoo-width80"'
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?>">
				<?php 
					esc_html_e('No results text', 'advanced-fuzzy-search'); 
				?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::text('search[no_results]', array(
							'value' => UtilsAfsw::getArrayValue($options, 'no_results', __('No products found', 'advanced-fuzzy-search'), 0, false, false, true),
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Search for a filtering results among all shop products on any shop pages and do not use shortcut settings on standard WooCommers pages.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Search among all products', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::checkboxToggle('search[all_products]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'all_products', false, 1)
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Select stockstatus', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Stockstatus', 'advanced-fuzzy-search'); ?>
				</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectlist('search[stockstatuses]', array(
							'options' => $stockValues,
							'attrs' => 'data-placeholder="' . __('Select...', 'advanced-fuzzy-search') . '"',
							'value' => UtilsAfsw::getArrayValue($options, 'stockstatuses'),
						));
						?>
				</div>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Select sorting', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Sort products', 'advanced-fuzzy-search'); ?>
				</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
						HtmlAfsw::selectBox('search[sorter]', array(
							'options' => $sortValues,
							'value' => UtilsAfsw::getArrayValue($options, 'sorter'),
						));
						?>
				</div>
			</div>
		</div>
<?php if (!$this->is_pro) { ?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Control keyboard layouts', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Check keyboard layout', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php HtmlAfsw::proOptionLink(); ?>
				</div>
			</div>
		</div>
	
<?php 
}
DispatcherAfsw::doAction('fieldsIncludeTpl', 'fieldsTabSearchBase', array('options' => $options)); 
?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Exclude/include products', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Enable exclude/include products', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Enable', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::checkboxToggle('search[e_exclude]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'e_exclude', false, 1)
						));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'searchExclude', array('options' => $options)); ?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Synonyms', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Enable synonyms. Note that the synonyms only works for the search modes: full words and part words.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Use synonyms', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::checkboxToggle('search[e_synonyms]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'e_synonyms', false, 1)
						));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'searchSynonyms', array('options' => $options)); ?>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bFull); ?> afsw-group-label">
				<?php esc_html_e('Stop words', 'advanced-fuzzy-search'); ?>
			</div>
		</div>
		<div class="row row-options-block">
			<div class="<?php echo esc_attr($bLabel); ?> woobewoo-tooltip" title="<?php esc_attr_e('Enable stop words. Note that the stop words only works for the search modes: full words and part words.', 'advanced-fuzzy-search'); ?>">
				<?php esc_html_e('Use stop words', 'advanced-fuzzy-search'); ?>
			</div>
			<div class="<?php echo esc_attr($bValues); ?>">
				<div class="options-value">
					<?php 
					if ($this->is_pro) {
						HtmlAfsw::checkboxToggle('search[e_stopwords]', array(
							'checked' => UtilsAfsw::getArrayValue($options, 'e_stopwords', false, 1)
						));
					} else {
						HtmlAfsw::proOptionLink();
					}
					?>
				</div>
			</div>
		</div>
<?php DispatcherAfsw::doAction('fieldsIncludeTpl', 'searchStopWords', array('options' => $options)); ?>
	</div>
</div>
<div class="woobewoo-clear"></div>
