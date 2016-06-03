<% include SideBar %>
<div class="content-container unit size3of4 lastUnit">
	<article>
		<h1>$Title</h1>

		<% loop $Elements %>
            <section>
                <h2>$Title</h2>

                <% if $ClassName = "TextContentElement" %>
                    $Content
                <% else_if $ClassName = "ImageContentElement" %>
                    $Image
                <% end_if %>
			</section>
		<% end_loop %>
	</article>
		$Form
		$CommentsForm
</div>