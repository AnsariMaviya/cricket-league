<div class="col-12">
	<div class="row row-cols-4">
		<?php 
		if(isset($teams)){
			foreach ($teams as $team) {
			?>
			<div class="col">
				<div class="card mt-4 text-center badge-dark">
					<div class="card-body">
						<h4 class="card-title"><?=$team->team_name?></h4>
						<p class="card-text"><?=$team->in_match?></p>
						<a href="<?=base_url()?>/players/<?=$team->team_id?>" class="card-link btn-primary btn">Players</a>
					</div>
				</div>
			</div>
			<?php 
			}
		}
		?>
	</div>
	
</div>