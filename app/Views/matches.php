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
					<th>Venue Name</th>
					<th>Venue Address</th>
					<th>Team 1</th>
					<th>Team 2</th>
					<th>Match Type</th>
					<th>Overs</th>
					<th>Team 1 Score</th>
					<th>Team 2 Score</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				if(isset($matches)){
					foreach ($matches as $match) {
						?>
						<tr>
							<td><?=$match->match_id?></td>
							<td><?=$match->name?></td>
							<td><?=$match->address?></td>
							<td><?=$match->team1Name?></td>
							<td><?=$match->team2Name?></td>
							<td><?=$match->match_type?></td>
							<td><?=$match->overs?></td>
							<td><?=$match->first_team_score?></td>
							<td><?=$match->second_team_score?></td>
						</tr>
						<?php 
					}
				}
				?>
			</tbody>
		</table>
	</div>
	
</div>