<div>
	<div class="p10">

		<table style="width:700px;">
			<tbody>
				<tr>
					<th class="" style="width:200px;height:35px">
					</th>
				    <td class="td_width text-shadow">
					    [{foreach from=$main_obj item=main_data key=obj_id}]
					    [{assign var=main_obj_id value=$obj_id}]
					    <strong class="ml5" style="position:relative;bottom:5px;">[{$main_data.link}]</strong><br />
					    <div class="ml5" id="vm_detail_[{$obj_id}]" >
						    <table class="matrixvalues">
						        <tr>
						            <td >RAM: [{$main_data.memory}] [{$main_data.memory_unit}]
						            </td>
						            <td>CPU: [{$main_data.cpu}] [{$main_data.cpu_unit}]
						            </td>
						            <td>DISK: [{$main_data.disc_space}] [{$main_data.disc_space_unit}]<img class="vam" src="images/icons/infobox/blue.png" title="[{isys type="lang" ident="LC__CMDB__CATG__OBJECT_VITALITY__DISK_INFO"}]">
						            </td>
						            <td>LAN: [{$main_data.bandwidth}] [{$main_data.bandwidth_unit}]<br />
						            </td>
						        </tr>
							</table>
						</div>
						[{/foreach}]
				    </td>
				</tr>
			</tbody>
		</table>
		<div id="matrix_scroller" style="overflow:auto;width:700px;max-height:205px;" >

			<table class="" style="width:700px;margin-top:0px" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
			[{foreach from=$c_members item="s" key="obj_id"}]
			<tr>
			  <th class="text-shadow" style="width:200px;height:45px">
			    <strong style="position:relative;bottom:5px;">[{$s.link}]</strong><br />
				    <div id="service_detail_[{$service_key}]" class="">
					    ([{isys type="lang" ident=$s.type}])<br />
					</div>
			  </th>
			  <td  class="td_width">
			    [{foreach from=$main_obj item=main_data key=obj_id}]
			    [{assign var=main_obj_id value=$obj_id}]
			    <div id="vm_detail_[{$obj_id}]" class="ml5">
				    <table class="matrixvalues">
				        <tr>
				            <td style="background:#fff">RAM: [{$s.memory}] [{$s.memory_unit}]<br />
				            </td>
					        <td style="background:#fff">CPU: [{$s.cpu}] [{$s.cpu_unit}]<br />
					        </td>
							<td style="background:#fff">DISK: [{$s.disc_space}] [{$s.disc_space_unit}]<br />
							</td>
							<td style="background:#fff">LAN: [{$s.bandwidth}] [{$s.bandwidth_unit}]<br />
							</td>
						</tr>
					</table>
				</div>
				[{/foreach}]
			  </td>
			</tr>
			[{/foreach}]
			</table>
		</div>
		<div style="width:700px;border-bottom: #9b9b9b solid 2px;"></div>
		<table class="" style="width:700px;" cellspacing="0" cellpadding="0">
			<tr>
			  <th class="text-shadow" style="width:200px;height:35px;">
			        <strong>[{isys type="lang" ident="LC__CMDB__CLUSTER_VITALITY__CONSUMPTION"}]</strong>
			  </th>
			  <td  class="td_width" style="background:#fff">
				  <div class="ml5">
				    <table class="matrixvalues" >
				        <tr>
			                <td style="background:#fff">RAM: [{$main_obj[$main_obj_id].memory_consumption}] [{$main_obj[$main_obj_id].memory_unit}]
				            </td>
							<td style="background:#fff">CPU: [{$main_obj[$main_obj_id].cpu_consumption}] [{$main_obj[$main_obj_id].cpu_unit}]
							</td>
							<td style="background:#fff">DISK: [{$main_obj[$main_obj_id].disc_space_consumption}] [{$main_obj[$main_obj_id].disc_space_unit}]
							</td>
							<td style="background:#fff">LAN: [{$main_obj[$main_obj_id].bandwidth_consumption}] [{$main_obj[$main_obj_id].bandwidth_unit}]
							</td>
						</tr>
					</table>
				  </div>
			  </td>
			</tr>
			<tr>
				<th class="text-shadow" style="width:200px;height:35px;">
			        <strong>[{isys type="lang" ident="LC__CMDB__OBJECT_VITALITY__REMAINING_RESOURCES"}]</strong>
			    </th>
				<td class="td_width" style="background:#fff">
					<div class="ml5">
						<table class="matrixvalues" >
				            <tr >
				                <td style="background:#fff;">
								[{if $main_obj[$main_obj_id].memory > 0}]
				                    [{assign var=memory_calc value=$main_obj[$main_obj_id].memory_rest*100/$main_obj[$main_obj_id].memory}]
								[{else}]
									[{assign var=memory_calc value=0}]
							    [{/if}]
				                <p style="color: [{if $main_obj[$main_obj_id].memory_rest <= 0}]#E70000[{else}][{if $main_obj[$main_obj_id].memory_rest < $main_obj[$main_obj_id].memory*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$memory_calc|round:2}]%">
								RAM: [{$main_obj[$main_obj_id].memory_rest}] [{$main_obj[$main_obj_id].memory_unit}]
								</p>
					            </td>
								<td style="background:#fff">
								[{if $main_obj[$main_obj_id].cpu > 0}]
									[{assign var=cpu_calc value=$main_obj[$main_obj_id].cpu_rest*100/$main_obj[$main_obj_id].cpu}]
								[{else}]
									[{assign var=cpu_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].cpu_rest <= 0}]#E70000[{else}][{if $main_obj[$main_obj_id].cpu_rest < $main_obj[$main_obj_id].cpu*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$cpu_calc|round:2}]%">
								CPU: [{$main_obj[$main_obj_id].cpu_rest}] [{$main_obj[$main_obj_id].cpu_unit}]<br />
								</p>
								</td>
								<td style="background:#fff">
								[{if $main_obj[$main_obj_id].disc_space > 0}]
									[{assign var=disc_space_calc value=$main_obj[$main_obj_id].disc_space_rest*100/$main_obj[$main_obj_id].disc_space}]
								[{else}]
									[{assign var=disc_space_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].disc_space_rest <= 0}]#E70000[{else}][{if $main_obj[$main_obj_id].disc_space_rest < $main_obj[$main_obj_id].disc_space*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$disc_space_calc|round:2}]%">
								DISK: [{$main_obj[$main_obj_id].disc_space_rest}] [{$main_obj[$main_obj_id].disc_space_unit}]<br />
								</p>
								</td>
								<td style="background:#fff">
								[{if $main_obj[$main_obj_id].bandwidth > 0}]
									[{assign var=bandwidth_calc value=$main_obj[$main_obj_id].bandwidth_rest*100/$main_obj[$main_obj_id].bandwidth}]
								[{else}]
									[{assign var=bandwidth_calc value=0}]
								[{/if}]
								<p style="color: [{if $main_obj[$main_obj_id].bandwidth_rest <= 0}]#E70000[{else}][{if $main_obj[$main_obj_id].bandwidth_rest < $main_obj[$main_obj_id].bandwidth*0.2}]#FF9900[{else}]#00cc00[{/if}][{/if}]" title="[{$bandwidth_calc|round:2}]%">
								LAN: [{$main_obj[$main_obj_id].bandwidth_rest}] [{$main_obj[$main_obj_id].bandwidth_unit}]<br />
								</p>
								</td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</table>

		<div class="cb"></div>
	</div>
</div>
