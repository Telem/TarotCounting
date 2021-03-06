/**
 * Utility method for sorting tokens whenever one gets added/removed from there
 */
var sortTokens = function() {
	$('#tokens > .token').sort(function(a,b) {
		var typeOrder = ['.role', '.bid', '.achievement:not(.slam)', '.achievement.slam'];
		var i;
		var typeA = 0, typeB = 0;
		for (i = 0; i < typeOrder.length; i++) {
			if ($(a).is(typeOrder[i])) {
				typeA = i;
			}
			if ($(b).is(typeOrder[i])) {
				typeB = i;
			}
		}
		if (typeA != typeB) return typeA - typeB;
		return a.dataset.dbid - b.dataset.dbid;
	}).appendTo('#tokens');
};

var toggleCallee = function() {
	if ($('#allplayers .player.selected').size() >= 5) {
		$('.token.role[data-dbid=3]').removeClass('inactive');
	}
	else {
		$('.token.role[data-dbid=3]').addClass('inactive').appendTo('#tokens');
		sortTokens();
	}
};

var selectActivePlayer = function() {
	if (!$('#allplayers .player.active').hasClass('selected')) {
		$('#allplayers .player.active').removeClass('active');
		$('#allplayers .player.selected').first().addClass('active');
	}
};

var resetToken = function($token) {
	if (!$token.hasClass('unique')) {
		$token.remove();
	}
	else {
		$('#tokens').append($token);
	}
}

var resetTokens = function() {
	$('.player .token').each(function(i, elem) {
		resetToken($(elem));
	});
	sortTokens();
};

$(function(){
	$('#allplayers .player').click(function() {
		var wasActive = $(this).hasClass('active');
		var wasSelected = $(this).hasClass('selected');
    	$('.player.active').removeClass('active');
    	$(this).addClass('active');
	    
		var $this = $(this);
		//don't select/unselect when the player has a token
		if ($(this).find('.token').size() == 0 && (wasActive || !wasSelected)) {
			$this.toggleClass('selected');
			if ($this.hasClass('selected')) {
				$('#game_score').append('<li class="player" data-dbid="'+this.dataset.dbid+'"><span class="name">'+$this.find('.name').html()+'</span><span class="score">0</span></li>');
			}
			else {
				$('#game_score .player[data-dbid='+this.dataset.dbid+']').remove();
			}
			toggleCallee();
			selectActivePlayer();
		}
	});
});

/**
 * Token dragging
 */
$(function() {
	
	var mustDuplicateItem = function(dragElement, dropTarget) {
		return !dragElement.hasClass('unique');
	};
	
	/*
	 * Returns the jQuery items that must be removed from dropTarget to drop dragElement
	 */
	var tokensForExchange = function(dragElement, dropTarget) {
		if (dragElement.hasClass('exclusive')) {
			return dropTarget.find('.token.'+dragElement.attr('data-exclusion-class'));
		}
		return $('');
	};
	
	var reassignAttacker = function() {
		var highestBid = null;
		$('#allplayers .token.bid').each(function(i, elem) {
			if (!highestBid || parseInt(elem.dataset.dbid) > parseInt(highestBid.dataset.dbid)) {
				highestBid = elem;
			}
		});
		if (highestBid) {
			$(highestBid).parent().append($('.token.role[data-dbid='+Tarot.roles.Attacker.id+']'));
		}
	};
	
	$('#tokens').on('click', '.token', function() {
		var $this = $(this);
		if (!$this.hasClass('unique') && $this.parent().prop('id') == 'tokens') {
			var clone = $(this).clone();
			$('#tokens').append(clone);
		}
		
		var tokensToRemove = tokensForExchange($(this), $('.player.active'));
		tokensToRemove.filter(':not(.unique)').remove();
		$('#tokens').append(tokensToRemove.filter('.unique'));
		
		$('.player.active').children('.tokens-container').append($this);
		
		reassignAttacker();
		
		sortTokens();
	});
	
	$('#allplayers').on('click', '.token', function() {
		resetToken($(this));
		sortTokens();
	});
	
	//ensure initial consistency
	sortTokens();
});

$(function() {

	var showGameResults = function(results) {
		$(results).each(function(i, result) {
			$('#game_score .player[data-dbid='+result.player_id+'] .score').html(result.score);
		})
	};

	var addPlayerScores = function(results) {
		$(results).each(function(i, result) {
			var $score = $('#allplayers .player[data-dbid='+result.player_id+'] .score');
			$score.html(parseInt($score.html()) + result.score);
		})
	};

	$('input[name=score_submission]').click(function() {
		var submission = {
			players: [],
			contract: parseInt($('select[name=contract]').prop('value')),
			score: parseInt($('input[name=score]').prop('value'))
		};
		
		$('#allplayers .player.selected').each(function(i, player) {
			var $bid = $(player).find(' .bid');
			var $role = $(player).find(' .role');
			
			var newPlayer = {
				id: player.dataset.dbid,
				bid: $bid.size() == 0?Tarot.bids['Pass'].id:$bid.prop('dataset').dbid,
				role: $role.size() == 0?Tarot.roles['Defender'].id:$role.prop('dataset').dbid,
				achievements: []
			};
			$(player).find(' .achievement').each(function(j, achievement) {
				newPlayer.achievements.push(achievement.dataset.dbid);
			});
			submission.players.push(newPlayer);
		});
		
		$.ajax({
			url:'submit.php',
			type: 'POST',
			contentType: 'application/json; charset=utf-8',
			data: JSON.stringify(submission),
			dataType: 'json',
			error: function(a,b,error) {
				$('.submission_notice').removeClass('success').addClass('error').html('Failed to submit because ['+error+']');
			},
			success: function(data) {
				resetTokens();
				showGameResults(data);
				addPlayerScores(data);
				$('.submission_notice').addClass('success').removeClass('error').html('Score saved');
			}
		});
	});
});

