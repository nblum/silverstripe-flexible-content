<% control $Elements %>
	<% if $ReadableIdentifier %>
        <a name="$ReadableIdentifier"></a>
	<% end_if %>
	<% if $Title %>
        <h2>$Title</h2>
	<% end_if %>
	$Me
<% end_control %>