
jQuery(document).ready(function($){

	var frontSetup = function (quizuDom){

		quizuElem = quizuDom;
		quizuId = quizuElem.attr('data-id');

		quizuBase = quizuObj['base'];

		quizPaths = quizuBase;
		selQuestion = {};
		optIds = {};

		selResult = {};
		selResult['scoreTrayectory'] = {};

		quizResults = {};
		quizProgressBase = {};

		summScores = {};
		summScores['total'] = {};
		summScores['highest'] = {};

		currentQuestion = quizuElem.find('.next').attr('data-current');
		currentPath = quizuElem.find('.next').attr('data-path');

		if(typeof localStorage.quizuQuizesResults !== 'undefined'){

			quizResults = JSON.parse(localStorage.quizuQuizesResults);

			if (quizuId in quizResults) {
				selResult = quizResults[quizuId];
			};

		}
	}

	
	var reloadQuiz = function (){

		sessionStorage.removeItem('quizuQuizProgress');

		quizCleanup();

		var quizuReload = $('<button class="reload" style="background-color: '+quizuObj.defaultColor+';">'+quizuObj.reset+'</button>');

		quizuElem.append(quizuReload);
		quizuElem.find('.question').html(quizuObj.error);

	}

	var quizCleanup = function(){
		quizuElem.find('.reset').remove();
		quizuElem.removeClass('multiple_choice');
		quizuElem.removeClass('essay_type');
		quizuElem.find('.result').remove();
		quizuElem.find('.jssocials').remove();
		quizuElem.find('.option_next').remove();
		quizuElem.find('.open_answer').remove();
		quizuElem.find('.score').remove();
		quizuElem.find('.option_score').remove();
		quizuElem.find('.email').remove();
	}

	
	var nextChecks = function (){

		isResult = false;
		
		linkId = quizuElem.find('.option_next.checked').attr('data-linkid');
		
		linkPath = quizuElem.find('.option_next.checked').attr('data-linkpath');

		if (typeof goFinalize !== 'undefined' && goFinalize == true) {
			linkId = 'finalize';
		}

		if (typeof questType !== 'undefined' && questType == 'multiple') {
			
			linkId = quizuElem.find('.option_next:first').attr('data-linkid');
			
			linkPath = quizuElem.find('.option_next:first').attr('data-linkpath');

			quizuElem.find('.option_next.checked').each(function(){
				optIds[$(this).attr('data-id')] = $(this).attr('data-id');
			});
		}

		if (quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['essay_flag'] == 'true') {

			essay_options = quizPaths['paths'][currentPath]['questions'][currentQuestion]['options']['essay']['essay_ops'];

			chosen_essay = quizuElem.find('.open_answer').val();

			$.each(essay_options, function(key_es, value_es){

				if (value_es['value'].toLowerCase() == chosen_essay.toLowerCase() && (value_es['link'])) {
					linkId = value_es['link']['linkid'];
					linkPath = value_es['link']['linkpath'];
				};
			});

		}

		if ((quizPaths['resultsCriteriaFlag'] == 'results_by_total' || quizPaths['resultsCriteriaFlag'] == 'results_by_option') && linkId in quizPaths['results']) {
			linkId = 'finalize';
		}
		
		if (linkId in quizPaths['results']) {
			
			isResult = true;
			
			selResult = quizPaths['results'][linkId];
			
			if (!selResult) {
				selResult = quizPaths['results'][quizResults[quizuId]['id']];
			};
		}

		if (
			!isResult 
		&&	linkPath in quizPaths['paths'] 
		&&	linkId in quizPaths['paths'][linkPath]['questions']
		) {
			selQuestion = quizPaths['paths'][linkPath]['questions'][linkId];
		}else{
			if (!isResult && !selQuestion) {
				reloadQuiz();
			};
			selQuestion = false;
		};

		if (linkId == 'finalize') {

			isResult = true;

			recordProgress();

			if (quizPaths['resultsCriteriaFlag'] == 'results_by_total') {
				$.each(quizPaths['results'], function(key, value){
					if (parseInt(value['score']['min']) <= parseInt(quizProgress['summScores']['total']) && parseInt(quizProgress['summScores']['total']) <= parseInt(value['score']['max'])) {
						$.extend(selResult, value);
						return false;
					}
				});
			};

			if (quizPaths['resultsCriteriaFlag'] == 'results_by_option') {
				$.each(quizPaths['results'], function(key, value){
					if (value['highest'] == quizProgress['summScores']['highest']) {
						$.extend(selResult, value);
						return false;
					}
				});
			};

			if (!selResult['id']) {
				$.extend(selResult, quizPaths['results'][Object.keys(quizPaths['results'])[0]]);
			};

		}else{
			recordProgress();
		};

		if(!isResult){

			if (!selQuestion) {

				findNextQuestion();

				if (linkPath == currentPath && selQuestion['id'] == quizPaths['paths'][currentPath]['questions'][Object.keys(quizPaths['paths'][currentPath]['questions'])[0]]['id']) {
					
					if (quizPaths['resultsCriteriaFlag'] == 'results_by_total' || quizPaths['resultsCriteriaFlag'] == 'results_by_option') {
						quizProgressBaseB = JSON.parse(sessionStorage.quizuQuizProgress);
						delete quizProgressBaseB[quizuId]['questions'][Object.keys(quizProgress['questions'])[Object.keys(quizProgress['questions']).length - 1]];
						sessionStorage.setItem('quizuQuizProgress', JSON.stringify(quizProgressBaseB));
						goFinalize = true;
						nextChecks();
						delete goFinalize;
					}else{
						selResult = quizPaths['results'][Object.keys(quizPaths['results'])[0]];
						if (!selResult) {
							reloadQuiz();
						}else{
							selResult['scoreTrayectory'] = quizProgress;	
							insertResult();
						}
					}
				}else{
					
					insertQuestion();
				};
				
			}else{
				insertQuestion();
			};
		}else{

			if (typeof selResult['id'] === 'undefined') {

				reloadQuiz();

			}else{
				insertResult();
			};

		}
	}

	var findNextQuestion = function (){
		if (quizPaths) {

			currentQuestion = quizuElem.find('.next').attr('data-current');
			currentPath = quizuElem.find('.next').attr('data-path');

  			quizuQuest = quizPaths['paths'][currentPath]['questions'];
			questionFound = false;

			$.each(quizuQuest, function(index, value){

				selQuestion = value;

				if (questionFound == true) {
					return false;
				};

				if (value.id == currentQuestion) {
					questionFound = true;
				};

			});

			pathFound = false;

			if (selQuestion.id == currentQuestion) {

				$.each(quizPaths['paths'], function(key, value){
					linkPath = value['id'];
					selQuestion = value['questions'][Object.keys(value['questions'])[0]];

					if (pathFound == true) {
						return false;
					};

					if (value['id'] == currentPath) {
						pathFound = true;
					};

				});

			}

		};
	}

	var insertQuestion = function (){

		if (!linkPath || linkPath == 'undefined' || typeof linkPath === 'undefined') {
			linkPath = currentPath;
		};

		currentColor = quizPaths['paths'][linkPath]['color'];

		quizCleanup();

		if (selQuestion['flags']['multiple_choice_flag'] == 'true') {
			quizuElem.addClass('multiple_choice');
		}else{
			quizuElem.removeClass('multiple_choice');
		}

		if (selQuestion['flags']['essay_flag'] == 'true') {
			quizuElem.addClass('essay_type');
		}else{
			quizuElem.removeClass('essay_type');
		}

		quizuElem.find('.question').html(selQuestion.title.nl2br());

		$.each(selQuestion.options, function(index, value){
			
			if (selQuestion['flags']['essay_flag'] == 'true' && value.id != 'essay') {
				return true;
			};

			if (selQuestion['flags']['essay_flag'] != 'true' && value.id == 'essay') {
				return true;
			};

			var input;
			var paragraph = '<p style="background-color: '+currentColor+';">'+value.value+'</p>';

			var quizuOption = $('<div></div>').attr({
				class: 'option_next',
				'data-id': 'essay',
				'data-result': 'false',
				value: quizuObj.next,
				'data-linkid': value.link.linkid,
				'data-linkpath': (value.link.linkpath == '' ? quizuElem.find('.next').attr('data-path') : value.link.linkpath)  
			});

			if (value.id == 'essay'){
				quizuOption.attr('data-id', 'essay');
				input = '<input type="text" class="open_answer" placeholder="'+value.value+'">'
				paragraph = '<p style="background-color: '+currentColor+';">'+quizuObj.next+'</p>'
			}else
			{
				quizuOption.attr('data-id', value.id);
			}

			if (value.img.url) {
				var quizuOptionImage = $('<img class="option_image" src="'+value.img.url+'" height="50" width="50">');
			};

			quizuOption.append(paragraph).append(quizuOptionImage);
			quizuElem.find('.next').before(quizuOption);
			quizuElem.find('.option_next').before(input);

			if (quizuElem.find('.open_answer').length > 0) {
				quizuElem.find('.open_answer').first().focus();
			};
		});

		quizuElem.find('.next').attr('data-current', selQuestion.id);
		quizuElem.find('.next').attr('data-path', (linkPath == '' || linkPath == 'undefined' || typeof linkPath === 'undefined'  ? quizuElem.find('.next').attr('data-path') : linkPath) );
		quizuElem.find('.next').css({'background-color': currentColor});
		quizuElem.find('.next').html(quizuObj.next);

	}

	var insertResult = function(){

		quizCleanup();

		quizuElem.find('.question').html(selResult.title.nl2br());

		if (selResult.content == '<p><br></p>' || selResult.content == '') {
			selResult.content = '';
			quizuElem.append('<div class="result no-margin">'+selResult.content+'</div>');
		}else{
			quizuElem.append('<div class="result">'+selResult.content+'</div>');
		}

		toInsertR = true;
		runStringTemplates();
		toInsertR = false;

		var quizuReset = $('<button class="reset" style="background-color: '+quizuObj.defaultColor+';">'+quizuObj.reset+'</button>');
		quizuElem.append(quizuReset);

		if (quizPaths['showScoresFlag'] == 'true') {
			quizuElem.find('.result').after('<p class="score">'+quizuObj.totalScore+'<strong>'+' '+selResult['scoreTrayectory']['summScores']['total']+'</strong></p>')

			if (quizPaths['resultsCriteriaFlag'] == 'results_by_option') {
				i = 1;

				sortedKeys = Object.keys(selResult['scoreTrayectory']['summScores']['options']).sort();

				$.each(sortedKeys, function(key, value){

					if (value == 'option_e') {
						quizuElem.find('.reset').before('<p class="option_score">'+quizuObj.optionEssay+' = '+'<strong>'+selResult['scoreTrayectory']['summScores']['options'][value]+'</strong></p>');
					}else{
						quizuElem.find('.reset').before('<p class="option_score">'+quizuObj.option+' '+(parseInt(value.substr(value.length - 1))+1)+' = '+'<strong>'+selResult['scoreTrayectory']['summScores']['options'][value]+'</strong></p>');
						i++;
					}

				});
				i = 0;
			};
		};
		
		if (quizuObj.flags.socialSharingFlag == 'true') {
			quizuElem.find('.reset').before('<div class="jssocials"></div>');
			quizuElem.find('.reset').after('<button class="email input">'+quizuObj.email+'</div>');

			var sharetypes = ["googleplus", "facebook", "twitter"];

			if (/Mobi/.test(navigator.userAgent)) {
			    sharetypes.push("email");
			    sharetypes.push({share: "whatsapp", logo: "fa fa-phone"});
			}

			if (typeof jsSocials !== 'undefined') {
				quizuElem.find('.jssocials').jsSocials({
					url: window.location.href,
					shares: sharetypes,
					showCount: false,
					showLabel: false,
					shareIn: "popup",
					text: quizuObj.share + "\n\n"
				});
			};

		};

		quizResults[quizuId] = selResult;
		localStorage.setItem('quizuQuizesResults', JSON.stringify(quizResults));
		
	}

	var recordProgress = function(){
		quizProgress = {};
		quizProgress['questions'] = {};
		quizProgress['summScores'] = {};
		quizProgress['summScores']['options'] = {};

		if (sessionStorage.getItem('quizuQuizProgress')) {
			quizProgressBase = JSON.parse(sessionStorage.getItem('quizuQuizProgress'));
			if (quizProgressBase[quizuId]) {
				quizProgress = $.extend(true, {} ,quizProgressBase[quizuId]);
			};
		}

		progressCurrent = quizuElem.find('.next').attr('data-current');
		progressPath = quizuElem.find('.next').attr('data-path');

		progressCount = 0;

		progressCurrentOptions = $.extend(true, {}, quizPaths['paths'][progressPath]['questions'][progressCurrent]['options']);
		progressOptionCount = 0;

		progressCount = Object.keys(quizProgress['questions']).length;

		quizProgress['questions']['question_'+progressCount] = {};
		quizProgress['questions']['question_'+progressCount]['option'] = {};

		quizProgress['questions']['question_'+progressCount]['id'] = progressCurrent;

		$.each(progressCurrentOptions, function(key, value){

			if (value.id in optIds) {
				progressOptionFound = value;
				optionCounted = progressOptionCount;
				if (progressOptionFound['id'] == 'essay') {
					optionCounted = 'e';
				}
				quizProgress['questions']['question_'+progressCount]['option'][value.id] = {};
				quizProgress['questions']['question_'+progressCount]['option'][value.id] = progressOptionFound;
				quizProgress['questions']['question_'+progressCount]['option'][value.id]['place'] = optionCounted;
			};

			if (value.id != 'essay') {
				progressOptionCount++;
			};

		});

		progressOptionCount = 0;
		progressCount = 0;
		quizProgress['summScores']['total'] = 0;

		$.each(quizProgress['summScores']['options'], function(key, value){
			quizProgress['summScores']['options'][key] = 0;
		});

		if (!quizProgress['summScores']) {
			quizProgress['summScores']['highest'] = 'option_0';
		}

		$.each(quizProgress['questions'], function(key_qu, value_qu){
			$.each(value_qu['option'], function(key_op, value){

				if (!quizProgress['summScores']['options']['option_'+value.place]) {
					quizProgress['summScores']['options']['option_'+value.place] = 0;
				};

				if (value.id == 'essay') {
					$.each(value.essay_ops, function(key_es, value_es){

						quPlace = parseInt(key_qu.split("_").pop());
						quLength = Object.keys(quizProgress['questions']).length - 1;

						correctAnswer = false;

						if (
							openAnswer.toLowerCase() == value_es.value.toLowerCase() 
							&& quPlace == quLength
							&& value_qu['id'] == currentQuestion
						) {
							quizProgress['questions'][key_qu]['option'][key_op]['essay_ops'][key_es]['correct'] = true;
							correctAnswer = true;
						};

						if (value_es['correct'] == true) {
							correctAnswer = true;
						};

						if (correctAnswer == true) {
							quizProgress['summScores']['options']['option_'+value.place] += parseInt(value_es.score);
							quizProgress['summScores']['total'] += parseInt(value_es.score);
						};
					});
				}else{
					quizProgress['summScores']['options']['option_'+value.place] += parseInt(value.score);
					quizProgress['summScores']['total'] += parseInt(value['score']);
				}
			});
		});

		openAnswer = '';

		sortedKeys = Object.keys(quizProgress['summScores']['options']).sort();

		$.each(sortedKeys, function(key, value){
			if (parseInt(quizProgress['summScores']['options'][value]) >= (typeof quizProgress['summScores']['options'][quizProgress['summScores']['highest']] !== 'undefined' ? quizProgress['summScores']['options'][quizProgress['summScores']['highest']] : 0)) {
				quizProgress['summScores']['highest'] = value;
			};
		});

		quizProgressBase[quizuId] = quizProgress;

		if (isResult) {
			selResult['scoreTrayectory'] = quizProgress;
			delete quizProgressBase[quizuId];
		};

		sessionStorage.setItem('quizuQuizProgress', JSON.stringify(quizProgressBase));
		
	}

	var runStringTemplates = function(){

		title = quizuElem.find('.question').text();
		result = quizuElem.find('.result').text();
		quiz = quizuElem.find('h3').text();
		email = quizuElem.find('.email.address').change().val();

		if (
			typeof email !== 'undefined' 
			&& email != quizuObj['userEmail'] 
		)
		{
			quizuObj['userEmail'] = email;
		}

		var frontStringTemplates = {
			'{{user-email}}' : quizuObj['userEmail'],
			'{{result-title}}' : title,
			'{{result-content}}' : result,
		};

		$.each(quizuObjOrT, function(obj, string){

			$.each(frontStringTemplates, function(index, value){
				string = string.split(index).join(value);
				quizuObj[obj] = string;
			});

		});
	}

	String.prototype.nl2br = function()
	{
	    return this.replace(/\n/g, "<br />");
	}

	$(window).on('unload', function(){
	  sessionStorage.removeItem('quizuQuizProgress');
	});

	$('.quizu_widget').each(function(){
		
		frontSetup($(this));

		runStringTemplates();

		if (quizuId in quizResults) {
			insertResult();
		};

		quizuElem.addClass('rendered');

		quizuElem.on('click','.option_next',function(event){

			event.preventDefault();

			frontSetup($(this).closest('.quizu_widget'));

			if ($(this).attr('data-id') == 'essay' ) {
				if (!quizuElem.find('.open_answer').change().val()) {
					alert(quizuObj.essayError);
					return;
				}else{
					openAnswer = $(this).closest('.quizu_widget').find('.open_answer').val();			
				}
			};

			if ($(this).hasClass('checked')) {
				currentColor = $(this).find('p').css('outlineColor');
				props = {
					'background-color' : currentColor,
					'color' : '',
				};
				$(this).removeClass('checked');
				$(this).find('p').css(props);
			}else{
				currentColor = $(this).find('p').css('backgroundColor');
				props = {
					'color' : currentColor,
					'box-shadow' : 'inset 0px 0px 0px 2px '+currentColor,
					'background-color' : '',
				};
				$(this).addClass('checked');
				$(this).find('p').css(props);
			}

			optIds[$(this).attr('data-id')] = $(this).attr('data-id');

			if (
				quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] != 'true'
				|| (
					quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] == 'true'
					&& quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['essay_flag'] == 'true'
					)
				) {
				nextChecks();
			};

		});

		quizuElem.on('click','.next',function(event){

			event.preventDefault();

			if ($(this).closest('.quizu_widget').find('.option_next.checked').length == 0) {
				alert(quizuObj.checkedError);
				return;
			};

			frontSetup($(this).closest('.quizu_widget'));

			if (quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] == 'true') {
				questType = 'multiple';
				nextChecks();
				delete questType;
			};

		});

		quizuElem.on('keypress', '.open_answer', function (e) {
	        if(e.which === 13){
				frontSetup($(this).closest('.quizu_widget'));
	        	quizuElem.find('.option_next').click();
	        }
	   	});

   		quizuElem.on('keypress', '.email.address', function (e) {
   	        if(e.which === 13){
				frontSetup($(this).closest('.quizu_widget'));
   	        	quizuElem.find('.send.email').click();
   	        }
   	   	});

		quizuElem.on('click', '.email.input', function(event){

			frontSetup($(this).closest('.quizu_widget'));

			$(this).removeClass('input').addClass('send');
			$(this).text(quizuObj.send);
			$(this).after('<input type="email" class="email address" placeholder="john-doe@example.com" value="'+quizuObj.userEmail+'" autocomplete="on">');

		});

		quizuElem.on('click', '.send.email', function(event){

			frontSetup($(this).closest('.quizu_widget'));

			quizuElem.find('.send.email').attr('disabled', 'true');

			var scores = {};
			scores['main'] ={};
			scores['options'] ={};

			scores['main'] = quizuElem.find('.score').html();


			quizuElem.find('.option_score').each(function(index){
				scores['options'][index] = $(this).html();
			});

			emailNonce = quizuElem.attr('data-email');

			runStringTemplates();

			that = $(this);

			$.ajax({
				url:   quizuObj.ajaxurl,
				data: {action : 'quizu_front_ajax', _ajax_nonce : emailNonce, command : 'email', email : email, title : title, subject : quizuObj['emailSubject'], message : quizuObj['emailMessage'], content : result, quiz : quiz, scores : scores, quizu_id : quizuId},
				type: 'POST',
				dataType: 'json',

				beforeSend: function(xhr){
					that.html('<i class="fa fa-spinner fa-spin"></i>');
				},
				success: function(data){

					if (data.success == true) {
						quizuElem.find('.send.email').remove();
						quizuElem.find('.email.address').remove();
						quizuElem.find('.email.error').remove();
						quizuElem.find('.reset').after('<p class="post email">'+quizuObj.postEmail+'</p>');
					};

					if (data.success == false) {
						that.html(quizuObj.send);
						that.removeAttr('disabled');
						quizuElem.find('.email.error').remove();
						that.after('<p class="email error">'+quizuObj.emailError+'</p>');
					};

				},
				complete: function(xhr, textStatus){

				}
			});
		});

   	   	quizuElem.on('click', '.reset',function(event){

   	   		event.preventDefault();
   	   		
   	   		frontSetup($(this).closest('.quizu_widget'));

   	   		var quizuQuest = quizPaths['paths']['default']['questions'];
   	   		
   	   		for(key in quizuQuest){
   	   			selQuestion = quizPaths['paths']['default']['questions'][key];
   	   			break;
   	   		}

   	   		quizCleanup();

   	   		linkPath = 'default';

   	   		insertQuestion();

   	   		delete quizResults[quizuId];
   	   		localStorage.setItem('quizuQuizesResults', JSON.stringify(quizResults));

   	   	});

   	   	quizuElem.on('click', '.reload',function(event){

   	   		event.preventDefault();
   	   		
   	   		delete quizResults[quizuId];
   	   		localStorage.setItem('quizuQuizesResults', JSON.stringify(quizResults));

   	   		location.reload();

   	   	});
	});

});