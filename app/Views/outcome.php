<style type="text/css">
	/*td{
		font-size: 20px;
		font-weight: 700
	}*/
</style>
<div class="col-12">
	<div class="row">
		<table style="width: 100%; margin: auto;" class="mt-5 table-info table table-hover text-center table-striped table">
			<thead>
				<tr>
					<th>Match ID</th>
					<th>VS</th>
					<th>Match Type</th>
					<th>Overs</th>
					<th>Team 1 Score</th>
					<th>Team 2 Score</th>
					<th>Outcome</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				if(isset($matches)){
					foreach ($matches as $match) {
						?>
						<tr>
							<td><?=$match->match_id?></td>
							<td><?=$match->team1Name?> vs <?=$match->team2Name?></td>
							<td><?=$match->match_type?></td>
							<td><?=$match->overs?></td>
							<td><?=$match->first_team_score?></td>
							<td><?=$match->second_team_score?></td>
							<td><?=$match->outcome?></td>
						</tr>
						<?php 
					}
				}
				?>
			</tbody>
		</table>
	</div>
	
</div>