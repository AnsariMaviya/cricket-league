<div class="col-12">
	<div class="row row-cols-4">
		<?php 
		if(isset($venues)){
			foreach ($venues as $venue) {
			?>
			<div class="col">
				<div class="card mt-4 text-center bg-secondary">
					<div class="card-body">
						<h4 class="card-title"><?=$venue->name?></h4>
						<p class="card-text"><?=$venue->address?></p>
						<a href="<?=base_url()?>/matches/<?=$venue->venue_id?>" class="card-link btn-primary btn">Matches</a>
					</div>
				</div>
			</div>
			<?php 
			}
		}
		?>
	</div>
	
</div>