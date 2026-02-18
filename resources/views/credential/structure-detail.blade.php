<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STRUCTURE LISTS :: {{ $dm->Name }} - {{ $dm->Emp_ID }}</div>
	<div class="card" id="ContentPages">
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<input type="hidden" name="ID" id="ID" value="{{ $dm->id }}" />
					<div class="form-group">
						<label for="PR_Approval">PR Approval</label>
						{{-- <input type="checkbox" id="PR_Approval" name="PR_Approval" @if($dm->PR_Approval) checked @endif> --}}
						<input type="text" id="PR_Approval" name="PR_Approval" class="form-control" value="{{ $dm->PR_Approval }}" />
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="BP_Approval">BP Approval</label>
						<input type="checkbox" id="BP_Approval" name="BP_Approval" @if($dm->BP_Approval) checked @endif>
					</div>
				</div>
				<div class="col-12">
					<button class="btn btn-success" onclick="submitkan()">Submit</button>
				</div>
			</div>

		</div>
	</div>
<div id='divrolesetingaja'></div>

<script>

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}

	function submitkan() {
		setTimeout(function(){
				
			dapat = getAllValues();
			$.ajax({
				headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
				},
			
				url : "credential-structure-detail-submit?_token={{ csrf_token() }}",
				type: "POST",
				data : dapat,
				success: function(data, textStatus, jqXHR)
				{
					if (data == 'success'){
						
						setTimeout(function(){
							alert('Credential Updated!');
							location.href='credential-structure';
						},1000);			

					}else{
						alert(data);
					}
				},
				error: function (jqXHR, textStatus, errorThrown)
				{
				alert( "error" );
			
				}
			});
		},1000);
					
	}

</script>
