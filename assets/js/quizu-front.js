
jQuery(document).ready(function($){

	var frontSetup = function (quizuDom){/*This function sets up some basic variables*/

		quizuElem = quizuDom;
		quizuId = quizuElem.attr('data-id');/*Get current Quiz ID*/
		quizuFirstNonce = quizuElem.attr('data-first');/*Get action nonce*/

		quizBase = {};
		selQuestion = {};
		optIds = {};

		selResult = {};
		selResult['scoreTrayectory'] = {};

		quizResults = {};/*If not, define as an empty object*/
		quizProgressBase = {};

		summScores = {};
		summScores['total'] = {};
		summScores['highest'] = {};

		currentQuestion = quizuElem.find('.next').attr('data-current');/*Get current question ID*/
		currentPath = quizuElem.find('.next').attr('data-path');/*Get current path ID*/

		if(typeof localStorage.quizuQuizesQuestions !== 'undefined'){/*Check if there are questions stored*/
			quizBase = JSON.parse(localStorage.quizuQuizesQuestions);/*If there are, retrieve questions*/
		}

		quizPaths = quizBase[quizuId];/*If there are, retrieve questions*/

		if(typeof localStorage.quizuQuizesResults !== 'undefined'){/*Check if there are user results stored*/

			quizResults = JSON.parse(localStorage.quizuQuizesResults);/*If there are, retrieve user results*/

			if (quizuId in quizResults) {/*Check if there are user results for this quiz*/
				selResult = quizResults[quizuId];/*If there are, select result*/
			};

		}
	}

	var quizuFrontGetQuiz = function (){/*This function is used to download quiz questions*/
		$.ajax({/*Questions are requested via AJAX*/
			url:   quizuObj.ajaxurl,
			data: {action : 'quizu_front_ajax', _ajax_nonce : quizuFirstNonce, quizu_id : quizuId, command : 'first'},
			type: 'POST',
			dataType: 'json',

			beforeSend: function(xhr){
				delete quizBase[quizuId];
				quizuElem.find('.reset').attr('disabled');
			},
			success: function(data){
				
				quizBase[data.id] = data;/*Insert/update Quiz data in questions object*/
				quizBase[data.id]['timestamp'] = new Date();/*Insert/update Quiz data in questions object*/
				quizPaths = quizBase[data.id];/*Insert/update Quiz data in questions object*/
				localStorage.setItem('quizuQuizesQuestions', JSON.stringify(quizBase));/*Store updated Quiz data*/
				frontSetup(quizuElem);
				quizuElem.find('.reset').html(quizuObj.reset);
			},
			complete: function(xhr, textStatus){
				quizuElem.find('.reset').removeAttr('disabled');
			}
		});
	}

	/*This function downloads and replaces Quiz data and allows user to reset quiz*/
	var quizuFrontReloadQuiz = function (){

		sessionStorage.removeItem('quizuQuizProgress');

		quizCleanup();

		var quizuReset = $('<button disabled class="reset" style="background-color: '+quizuObj.defaultColor+';">'+quizuObj.reset+'</button>');/*Create reset button*/

		quizuElem.append(quizuReset);/*Append reset button*/
		quizuElem.find('.question').html(quizuObj.error);/*Append error message*/

		quizuFrontGetQuiz();/*Download Quiz data*/

	}

	var quizCleanup = function(){
		quizuElem.find('.reset').remove();/*Remove reset button*/
		quizuElem.removeClass('multiple_choice');/*Remove reset button*/
		quizuElem.removeClass('essay_type');/*Remove reset button*/
		quizuElem.find('.result').remove();/*Remove result content*/
		quizuElem.find('.jssocials').remove();/*Remove result content*/
		quizuElem.find('.option_next').remove();/*Previous question's data cleanup*/
		quizuElem.find('.open_answer').remove();/*Previous question's data cleanup*/
		quizuElem.find('.score').remove();/*Result's data cleanup*/
		quizuElem.find('.option_score').remove();/*Result's data cleanup*/
		quizuElem.find('.email').remove();/*Previous question's data cleanup*/
	}

	/*This function organizes the variables needed to determine the next question*/
	var quizuFrontNextChecks = function (type){

		isResult = false;

		/*Get next question/result ID*/
		linkId = quizuElem.find('.option_next.checked').attr('data-linkid');
		/*Get next question path*/
		linkPath = quizuElem.find('.option_next.checked').attr('data-linkpath');

		if (typeof goFinalize !== 'undefined' && goFinalize == true) {
			linkId = 'finalize';
		}

		if (type == 'multiple') {

			/*Get next question/result ID*/
			linkId = quizuElem.find('.option_next:first').attr('data-linkid');
			/*Get next question path*/
			linkPath = quizuElem.find('.option_next:first').attr('data-linkpath');

			quizuElem.find('.option_next.checked').each(function(){
				optIds[$(this).attr('data-id')] = $(this).attr('data-id');/*Mark selected option*/
			});

		}

		if (quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['essay_flag'] == 'true') {

			essay_options = quizPaths['paths'][currentPath]['questions'][currentQuestion]['options']['essay']['essay_ops'];

			chosen_essay = quizuElem.find('.open_answer').val();

			$.each(essay_options, function(key_es, value_es){

				if (value_es['value'].toLowerCase() == chosen_essay.toLowerCase() && (value_es['link'])) {
					/*Get next question/result ID*/
					linkId = value_es['link']['linkid'];
					// Get next question path
					linkPath = value_es['link']['linkpath'];
				};
			});

		}

		if ((quizPaths['resultsCriteriaFlag'] == 'results_by_total' || quizPaths['resultsCriteriaFlag'] == 'results_by_option') && linkId in quizPaths['results']) {
			linkId = 'finalize';
		}
		/*Check if next question is a result*/
		if (linkId in quizPaths['results']) {
			/*IF it IS*/
			/*Set boolean flag to true*/
			isResult = true;

			/*Get result from quiz data using linked ID*/
			selResult = quizPaths['results'][linkId];
			/*Check if result was succesfully retrieved*/
			if (!selResult) {
				/*IF its NOT*/
				/*Get result from quiz data using locally stored result ID*/
				selResult = quizPaths['results'][quizResults[quizuId]['id']];
			};
		}

		if (/*Check if its a result and if it exists among the quiz' questions*/
			!isResult 
		&&	linkPath in quizPaths['paths'] 
		&&	linkId in quizPaths['paths'][linkPath]['questions']
		) {/*If it does, select question*/
			selQuestion = quizPaths['paths'][linkPath]['questions'][linkId];
		}else{
			if (!isResult && !selQuestion) {/*If it doesnt, display error and download quiz data again*/
				quizuFrontReloadQuiz();
			};
			selQuestion = false;
		};

		if (linkId == 'finalize') {

			isResult = true;

			quizuFrontRecordProgress();

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
			quizuFrontRecordProgress();
		};

		if(!isResult){/*Check if current question is NOT a result*/

			if (!selQuestion) {/*Check if a question is selected*/

				quizuFrontFindNextQuestion();

				if (linkPath == currentPath && selQuestion['id'] == quizPaths['paths'][currentPath]['questions'][Object.keys(quizPaths['paths'][currentPath]['questions'])[0]]['id']) {/*If retrieved question is same as current one, display error message and download quiz data*/
					
					if (quizPaths['resultsCriteriaFlag'] == 'results_by_total' || quizPaths['resultsCriteriaFlag'] == 'results_by_option') {
						quizProgressBaseB = JSON.parse(sessionStorage.quizuQuizProgress);
						delete quizProgressBaseB[quizuId]['questions'][Object.keys(quizProgress['questions'])[Object.keys(quizProgress['questions']).length - 1]];
						sessionStorage.setItem('quizuQuizProgress', JSON.stringify(quizProgressBaseB));
						goFinalize = true;
						quizuFrontNextChecks();
						goFinalize = false;
					}else{
						selResult = quizPaths['results'][Object.keys(quizPaths['results'])[0]];
						if (!selResult) {
							quizuFrontReloadQuiz();
						}else{
							selResult['scoreTrayectory'] = quizProgress;	
							quizuFrontInsertResult();
						}
					}
				}else{/*If it is a different question, insert it*/
					/*Find next question*/
					quizuFrontInsertQuestion();
				};
				
			}else{
				quizuFrontInsertQuestion();/*If a question is already selected, insert question*/
			};
		}else{/*IF it IS a result, insert it*/

			if (typeof selResult['id'] === 'undefined') {/*If retrieved question is same as current one, display error message and download quiz data*/

				quizuFrontReloadQuiz();/*Reload quiz data*/

			}else{/*If it is a different question, insert it*/
				/*Find next question*/
				quizuFrontInsertResult();
			};

		}

		quizuFrontRunStringTemplates();

	}

	var quizuFrontFindNextQuestion = function (){/*This function finds the next question in the same path when linked question is not found*/
		if (quizPaths) {/*If questions array exists*/

			currentQuestion = quizuElem.find('.next').attr('data-current');/*Get current question ID*/
			currentPath = quizuElem.find('.next').attr('data-path');/*Get current path ID*/

  			var quizuQuest = quizPaths['paths'][currentPath]['questions'];/*Get current question data*/
			var questionFound = false;/*Question found boolean flag*/

			$.each(quizuQuest, function(index, value){

				selQuestion = value;/*Select the question being looped over*/

				if (questionFound == true) {/*If the count helpers match, stop here to retrieve the next question*/
					return false;
				};

				if (value.id == currentQuestion) {/*When the current question is looped over, match helpers to select next question*/
					questionFound = true;
				};

			});

			pathFound = false;

			if (selQuestion.id == currentQuestion) {/*If retrieved question is same as current one, display error message and download quiz data*/

				$.each(quizPaths['paths'], function(key, value){
					linkPath = value['id'];/*Select the question being looped over*/
					selQuestion = value['questions'][Object.keys(value['questions'])[0]];/*Select the question being looped over*/

					if (pathFound == true) {/*If the count helpers match, stop here to retrieve the next question*/
						return false;
					};

					if (value['id'] == currentPath) {/*When the current question is looped over, match helpers to select next question*/
						pathFound = true;
					};

				});

			}

		};
	}

	var quizuFrontInsertQuestion = function (){/*This function inserts found questions over previous questions*/

		if (!linkPath || linkPath == 'undefined' || typeof linkPath === 'undefined') {
			linkPath = currentPath;
		};

		currentColor = quizPaths['paths'][linkPath]['color'];/*Get current path ID*/

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

		quizuElem.find('.question').html(selQuestion.title);/*Insert question title*/

		$.each(selQuestion.options, function(index, value){/*Create container for each option*/
			
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

			if (value.img.url) {/*Check if there is an image linked to this question, and display it*/
				var quizuOptionImage = $('<img class="option_image" src="'+value.img.url+'" height="50" width="50">');
			};

			quizuOption.append(paragraph).append(quizuOptionImage);/*Insert image*/
			quizuElem.find('.next').before(quizuOption);/*Insert option*/
			quizuElem.find('.option_next').before(input);/*Insert option*/
		});

		quizuElem.find('.next').attr('data-current', selQuestion.id);/*Update current question ID indicator*/
		quizuElem.find('.next').attr('data-path', (linkPath == '' || linkPath == 'undefined' || typeof linkPath === 'undefined'  ? quizuElem.find('.next').attr('data-path') : linkPath) );/*Update current question path indicator*/
		quizuElem.find('.next').css({'background-color': currentColor});/*Update current question path indicator*/
		quizuElem.find('.next').html(quizuObj.next);/*Update current question path indicator*/

	}

	var quizuFrontInsertResult = function(){/*This function inserts result's data*/

		quizCleanup();

		var quizuReset = $('<button class="reset" style="background-color: '+quizuObj.currentColor+';">'+quizuObj.reset+'</button>');/*Create reset button*/
		quizuElem.find('.question').html(selResult.title);/*Insert result title*/

		toInsertR = true;
		quizuFrontRunStringTemplates();
		toInsertR = false;

		if (selResult.content == '<p><br></p>' || selResult.content == '') {
			selResult.content = '';
			quizuElem.append('<div class="result no-margin">'+selResult.content+'</div>');/*Insert reset button*/
		}else{
			quizuElem.append('<div class="result">'+selResult.content+'</div>');/*Insert reset button*/
		}

		quizuElem.append(quizuReset);/*Insert reset button*/

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

		quizResults[quizuId] = selResult;/*Store user result in results object data*/
		localStorage.setItem('quizuQuizesResults', JSON.stringify(quizResults));/*Store updated result data*/
		
	}

	var quizuFrontRecordProgress = function(){

		quizProgress = {};
		quizProgress['questions'] = {};
		quizProgress['summScores'] = {};
		quizProgress['summScores']['options'] = {};

		if (sessionStorage.getItem('quizuQuizProgress')) {

			quizProgressBase = JSON.parse(sessionStorage.getItem('quizuQuizProgress'));

			if (quizProgressBase[quizuId]) {
				quizProgress = quizProgressBase[quizuId];
			};

		}

		progressCurrent = quizuElem.find('.next').attr('data-current');
		progressPath = quizuElem.find('.next').attr('data-path');

		progressCount = 0;

		progressCurrentOptions = quizPaths['paths'][progressPath]['questions'][progressCurrent]['options'];
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

		$.each(quizProgress['questions'], function(key, value_qu){
			$.each(value_qu['option'], function(key, value){

				if (!quizProgress['summScores']['options']['option_'+value.place]) {
					quizProgress['summScores']['options']['option_'+value.place] = 0;
				};

							console.log(value);
				if (value.id == 'essay') {
					$.each(value.essay_ops, function(key_es, value_es){
						if ((openAnswer.toLowerCase() == value_es.value.toLowerCase()) || (quizProgress['summScores']['options']['option_'+value.place]['answer'] == value_es.value.toLowerCase()) && value_qu['id'] == currentQuestion) {
							quizProgress['summScores']['options']['option_'+value.place] += parseInt(value_es.score);
							quizProgress['summScores']['total'] += parseInt(value_es.score);
							quizProgress['summScores']['options']['option_'+value.place]['answer'] = openAnswer.toLowerCase();
						};
					});
				}else{
					quizProgress['summScores']['options']['option_'+value.place] += parseInt(value.score);
					quizProgress['summScores']['total'] += parseInt(value['score']);
				}
			});
		});

		sortedKeys = Object.keys(quizProgress['summScores']['options']).sort();

		$.each(sortedKeys, function(key, value){
			if (parseInt(quizProgress['summScores']['options'][value]) >= (typeof quizProgress['summScores']['options'][quizProgress['summScores']['highest']] !== 'undefined' ? quizProgress['summScores']['options'][quizProgress['summScores']['highest']] : 0)) {
				quizProgress['summScores']['highest'] = value;
			};
		});

		quizProgressBase[quizuId] = quizProgress;

		sessionStorage.setItem('quizuQuizProgress', JSON.stringify(quizProgressBase));

		if (isResult) {

			selResult['scoreTrayectory'] = quizProgress;

		};

	}

	var quizuFrontRunStringTemplates = function(){

		title = quizuElem.find('.question').text();
		result = quizuElem.find('.result').text();
		quiz = quizuElem.find('h3').text();
		email = quizuElem.find('.email.address').change().val();

		var frontStringTemplates = {
			'{{user-email}}' : quizuObj.userEmail,
		};

		if (typeof email !== 'undefined' && email != quizuObj['userEmail'] && email != frontStringTemplates[quizuObj['userEmail']]) {
			frontStringTemplates[quizuObj['userEmail']] =  email;
		}

		if (typeof toInsertR !== 'undefined' && toInsertR == true){
			frontStringTemplates['{{result-title}}'] = title;
			frontStringTemplates['{{result-content}}'] = result;
		}

		$.each(quizuObj, function(obj, string){

			if (obj != 'flags') {
				$.each(frontStringTemplates, function(index, value){

					while(string.indexOf(index) !== -1) {
						string = string.replace(index, value);
						quizuObj[obj] = string;
					};

				});
			};

		});
	}

	/*In case any error happens, get quiz data once more*/
	window.onerror = quizuFrontGetQuiz;

	$(window).on('unload', function(){
	  sessionStorage.removeItem('quizuQuizProgress');
	});

	$('.quizu_widget').each(function(){
		
		frontSetup($(this));

		quizuFrontRunStringTemplates();

		if(!quizBase || !(quizuId in quizBase) && typeof quizuFirstNonce !== 'undefined'){/*If there are no results stored, download quiz data*/

			quizuFrontGetQuiz();

		}else{
			if (quizuId in quizResults) {/*If there are, check if a user result is already stored*/

				quizuFrontInsertResult();/*IF there IS, insert result*/

			};		
		};

		if (quizuObj.flags.isPreview == true) {
			quizuFrontGetQuiz();
		};

		quizuElem.addClass('rendered');

		quizuElem.on('click','.option_next',function(){/*This function handles passing from one question to another*/

			event.preventDefault();

			frontSetup($(this).closest('.quizu_widget'));

			if ($(this).attr('data-id') == 'essay' ) {
				if (!quizuElem.find('.open_answer').val()) {
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

			optIds[$(this).attr('data-id')] = $(this).attr('data-id');/*Mark selected option*/

			if (
				quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] != 'true'
				|| (
					quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] == 'true'
					&& quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['essay_flag'] == 'true'
					)
				) {
				quizuFrontNextChecks();/*Run checks*/
			};

		});

		quizuElem.on('click','.next',function(){/*This function handles passing from one question to another*/

			event.preventDefault();

			if ($(this).closest('.quizu_widget').find('.option_next.checked').length == 0) {
				alert(quizuObj.checkedError);
				return;
			};

			frontSetup($(this).closest('.quizu_widget'));

			if (quizPaths['paths'][currentPath]['questions'][currentQuestion]['flags']['multiple_choice_flag'] == 'true') {
				var type = 'multiple';
				quizuFrontNextChecks(type);/*Run checks*/
			};

		});

		quizuElem.on('click', '.reset',function(){/*This function handles quiz data reset button interactions*/

			event.preventDefault();
			
			frontSetup($(this).closest('.quizu_widget'));

			sessionStorage.removeItem('quizuQuizProgress');

			delete quizResults[quizuId];/*Remove user result from results object*/
			localStorage.setItem('quizuQuizesResults', JSON.stringify(quizResults));/*Store updated results*/

			var quizuQuest = quizPaths['paths']['default']['questions'];/*Get current quiz' questions*/

			for(key in quizuQuest){/*Select current quiz's first question*/
				selQuestion = quizPaths['paths']['default']['questions'][key];
				break;
			}

			quizCleanup();

			linkPath = 'default';/*Set linked path to default*/

			quizuFrontInsertQuestion();/*Insert first question*/

		});

		quizuElem.on('click', '.email.input', function(){

			frontSetup($(this).closest('.quizu_widget'));

			$(this).removeClass('input').addClass('send');
			$(this).text(quizuObj.send);
			$(this).after('<input type="email" class="email address" placeholder="john-doe@example.com" value="'+quizuObj.userEmail+'" autocomplete="on">');

		});

		quizuElem.on('click', '.send.email', function(){

			frontSetup($(this).closest('.quizu_widget'));

			quizuElem.find('.send.email').attr('disabled', 'true');

			emailNonce = quizuElem.attr('data-email');

			quizuFrontRunStringTemplates();

			that = $(this);

			$.ajax({/*Questions are requested via AJAX*/
				url:   quizuObj.ajaxurl,
				data: {action : 'quizu_front_ajax', _ajax_nonce : emailNonce, command : 'email', email : email, title : title, content : result, quiz : quiz, quizu_id : quizuId},
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

		quizuElem.on('keypress', '.email.address', function (e) {
	        if(e.which === 13){
	        	quizuElem.find('.send.email').click();
	        }
	   	});
	});

});