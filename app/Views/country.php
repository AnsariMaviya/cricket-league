<div class="col-12">
	<div class="row row-cols-4">
		<?php 
		if(isset($country)){
			foreach ($country as $country) {
			?>
			<div class="col">
				<div class="card mt-4 text-center">
					<div class="card-body">
						<h4 class="card-title"><?=$country->name?></h4>
						<p class="card-text"><?=$country->short_name?></p>
						<a href="<?=base_url()?>/teams/<?=$country->country_id?>" class="card-link btn-primary btn">Teams</a>
					</div>
				</div>
			</div>
			<?php 
			}
		}
		?>
	</div>
	
</div>