<script type="module/realm" use="localState, event">
	const [, value] = event;
	localState.set('is-btn-disabled', value === '');
</script>