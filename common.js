
$(function(){
	$('.tab').each(function(i, elem) {
		var groupName = this.dataset.groupname;
		var $tabSummaryLink = $('<div class="pseudolink">'+groupName+'</div>');
		$tabSummaryLink.click(function(){
			$('html, body').animate({
				scrollTop: $('.tab[data-groupname="'+groupName+'"]').offset().top
			}, 500);
		});
		$('.tab-summary').append($tabSummaryLink);
		$('<h3>'+groupName+'</h3>').insertBefore(this);
	});
});

