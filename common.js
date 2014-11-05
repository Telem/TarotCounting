
$(function(){
	$('.tab').each(function(i, elem) {
		var groupName = this.dataset.groupname;
		var groupID = encodeURIComponent(groupName);
		var $tabSummaryLink = $('<a href="#'+groupID+'" class="pseudolink">'+groupName+'</a>');
		$tabSummaryLink.click(function(){
			$('html, body').animate({
				scrollTop: $('.tab[data-groupname="'+groupName+'"]').offset().top
			}, 500);
		});
		$('.tab-summary').append($tabSummaryLink);
		$('<h3><a id="'+groupID+'">'+groupName+'</a></h3>').prependTo(this);
	});
});

