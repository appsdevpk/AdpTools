<set-state name="is-loading" value="true"></set-state>
<http-request url="https://realm.codes/api-examples/api.html" method="GET">
	<response-ok>
		<set-state name="content" from="event"></set-state>
		<set-state name="is-loading" value="false"></set-state>
	</response-ok>
	<response-fail>
		<set-state name="error" value="$.message" from="event"></set-state>
		<set-state name="is-loading" value="false"></set-state>
	</response-fail>
</http-request>
<script type="module/realm" use="localState,globalState,$,attr,attrs,ref,refs,event">
	setTimeout(function(){
		localState.set('age',190);
		localState.set('show-button',true);
	},5000);
</script>