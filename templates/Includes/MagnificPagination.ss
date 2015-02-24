<% with PaginatedItems %>
<% if MoreThanOnePage %>
<nav class="gallery-pagination">		
	<ul>
		<% if NotFirstPage %>
		<li class="previous"><a title="<% _t('MagnificGalleryPage.VIEWPREVIOUSPAGE','View the previous page') %>" href="$PrevLink">
				&laquo; <% _t('MagnificGalleryPage.PREVIOUS','Previous') %></a></li>
		<% else %>	
		<li class="previous-off">&laquo;<% _t('MagnificGalleryPage.PREVIOUS','Previous') %></li>
		<% end_if %>

		<% loop Pages %>
		<% if CurrentBool %>
		<li class="active">$PageNum</li>
		<% else %>
		<% if Link %>
		<li><a href="$Link" title="<% sprintf(_t('MagnificGalleryPage.VIEWPAGENUMBER','View page number %s'),$PageNum) %>">$PageNum</a></li>		
		<% else %>
		...
		<% end_if %>
		<% end_if %>
		<% end_loop %>

		<% if NotLastPage %>
		<li class="next"><a title="<% _t('MagnificGalleryPage.VIEWNEXTPAGE', 'View the next page') %>" href="$NextLink">
				<% _t('MagnificGalleryPage.NEXT','Next') %> &raquo;</a></li>
		<% else %>
		<li class="next-off"><% _t('MagnificGalleryPage.NEXT','Next') %> &raquo;</li>				
		<% end_if %>
	</ul>
</nav> 		
<% end_if %>
<% end_with %>