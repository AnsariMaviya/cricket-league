<div class="col-12">
	<div class="row row-cols-4">
		<?php 
		if(isset($players)){
			foreach ($players as $player) {
			?>
			<div class="col">
				<div class="card mt-4 text-center bg-secondary">
					<div class="card-body">
						<h4 class="card-title"><?=$player->name?></h4>
						<p class="card-text"><?=$player->team_name?></p>
						<a href="<?=base_url()?>/profiles/<?=$player->player_id?>" class="card-link btn-primary btn">Profiles</a>
					</div>
				</div>
			</div>
			<?php 
			}
		}
		?>
	</div>
	
</div>