<style>
	button{
		border-radius: var(--radius-2);
		padding: var(--size-fluid-1);
		box-shadow: var(--shadow-2);
		color: var(--blue-6);
		background-color: var(--blue-0);
	}
</style>
<strong>
	Hello world, my name is
	<slot name="@name"></slot>
	!
</strong>
<em>
	and, I'll live until
	<slot name="#age"></slot>
	years old
</em>
<is-visible value="#show-button" eq="true">
	<button ref="SetAgeButton">Increment the age</button>
</is-visible>
<div>
	<slot children></slot>
</div>

<is-visible value="#is-loading" eq="true">Fetching...</is-visible>
<is-hidden value="#is-loading" eq="true">
	<div>
		Response:
		<slot name="#content"></slot>
	</div>
	<div>
		Error:
		<slot name="#error"></slot>
	</div>
</is-hidden>