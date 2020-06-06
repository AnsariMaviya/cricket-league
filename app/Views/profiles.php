<style type="text/css">
	td{
		font-size: 20px;
		font-weight: 700
	}
</style>
<div class="col-12">
	<div class="row">
		<?php 
		if(isset($profiles)){
			foreach ($profiles as $player) {
			?>
			<div class="col-12">
				<div class="card mt-4 bg-white ">
					<img src="<?=base_url().'/'.$player->profile_image?>" style="width: 20%; border-radius: 20px; border: 2px dotted gray; margin: auto;" class="card-img-top" alt="">
					<div class="card-body">
						<table style="width: 50%; margin: auto;">
							<tr>
								<td>Player Name: </td>
								<td><?=$player->name?></td>
							</tr>
							<tr>
								<td>Player Country Name: </td>
								<td><?=$player->country_name?></td>
							</tr>
							<tr>
								<td>Player Country Short Name: </td>
								<td><?=$player->short_name?></td>
							</tr>
							<tr>
								<td>Player Team Name: </td>
								<td><?=$player->team_name?></td>
							</tr>
							<tr>
								<td>Player Date of Birth: </td>
								<td><?=date('d-m-Y',strtotime($player->dob))?></td>
							</tr>
							<tr>
								<td>Player Age: </td>
								<td><?=date('Y') - date('Y',strtotime($player->dob))?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<?php 
			}
		}
		?>
	</div>
	
</div>