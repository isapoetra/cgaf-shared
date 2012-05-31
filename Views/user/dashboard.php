<?php
use System\Web\UI\Items\MenuItem;
if ($items) {?>
<div class="row">
	<div class="span8">
		<?php
		if (isset ( $items->center )) {
			echo '<ul class="thumbnails">';
			foreach ( $items->center as $v ) {
				echo '<li class="span1">';
				$action = BASE_URL . $v->action;
				$icon = null;
				if ($v->icon) {
					$icon = $appOwner->getLiveAsset ( $v->icon );
				}
				if (! $icon) {
					$icon = \URLHelper::add(ASSET_URL,'images/').'gear.png';
				}
				echo '<a class="thumbnail" href="' . $action . '">';
				echo '<img src="' . $icon . '">';
				echo '</a>';
				echo '<div class="caption">';
				echo '<h5>' . $v->title . '</h5>';
				if (isset ( $v->descr )) {
					echo '<p>' . $v->descr . '</p>';
				}
				echo '</div>';
				echo '</li>';
			}
			echo '</ul>';
		}
		?>
	</div>
</div>
<?php
}
?>
<div class="row-fluid show-grid">
	<div class="span2"><?php
	echo '<ul class="dashboard-app nav nav-list">';
	$apps = AppManager::getInstalledApp(true);
	foreach($apps as $app) {
		echo '<li class="'.($app->app_id===AppManager::getActiveApp()  ? 'active' : '').'">';
		echo '<a href="'.URLHelper::add(BASE_URL,'user/dashboard/?__appId='.$app->app_id).'"><img src="'.BASE_URL.'/asset/images/icons/app/'.$app->app_id.'.png"/><span>'.$app->app_name.'</span></a>';
		echo '</li>';
	}
	echo '</ul>';
	?></div>
	<div class="span8">&nbsp;
		<?php echo $contents ? $contents : $this->getController()->renderContent('dashboard-center');?>
	</div>
	<div class="span2"><?php echo $this->getController()->renderContent('dashboard-right');?></div>
</div>
<div class="row">
<?php echo $this->getController()->renderContent('dashboard-bottom');?>
</div>