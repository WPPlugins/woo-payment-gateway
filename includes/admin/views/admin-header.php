<?php global $current_tab, $current_page;

$tabs = apply_filters('bfwc_admin_header_tabs', $tabs);
?>

<nav class="braintree-grey">
	<div class="nav-wrapper">
		<a href="#" class="brand-logo left">
			<img
				src="<?php echo bt_manager()->plugin_assets_path() . 'img/braintree-logo-white.svg'?>" />
		</a>
		<a href="#" data-activates="braintree-mobile-nav"
			class="button-collapse right">
			<i class="material-icons">menu</i>
		</a>
		<ul class="hide-on-med-and-down right">
		<?php foreach ($tabs as $tab):?>
			<li class="<?php if($current_tab === $tab['id']){?>active<?php }?>">
			<?php if(!empty($tab['page'])):?>
				<a
					href="<?php echo admin_url() . 'admin.php?page='.$tab['page'] . '&tab='.$tab['id']?>"><?php  echo $tab['label']?></a>
				<?php elseif (!empty($tab['url'])):?>
					<a target="_blank" href="<?php echo $tab['url']?>"><?php echo $tab['label']?></a>
			</li>
			<?php endif;?>
		<?php endforeach?>
		</ul>
		<ul class="side-nav" id="braintree-mobile-nav">
			<?php foreach ($tabs as $tab):?>
			<li class="<?php if($current_tab === $tab['id']){?>active<?php }?>">
			<?php if(!empty($tab['page'])):?>
				<a
					href="<?php echo admin_url() . 'admin.php?page='.$tab['page'] . '&tab='.$tab['id']?>"><?php  echo $tab['label']?></a>
				<?php elseif (!empty($tab['url'])):?>
					<a target="_blank" href="<?php echo $tab['url']?>"><?php echo $tab['label']?></a>
			</li>
			<?php endif;?>
		<?php endforeach?>
		</ul>
	</div>
</nav>