
jQuery(document).ready( function($) {

  adminSetup = function(){

    $('#quizu_questions').addClass('parent');

    quizuId = $('#quizu_id').val();/*Get current quiz ID*/

  }

  adminSetup();

  var getVariables = function(control, attachment){

    controller = control;/*Define for later use in ajax functions*/
    image = attachment;

    quizu_nonce = controller.attr('data-nonce');/*Get nonce*/
    
    command = controller.attr('data-command');
    path = controller.closest('.parent').attr('data-path');
    quparent = controller.closest('.parent').attr('data-question');
    option = controller.closest('.parent').attr('data-option');
    color = controller.closest('.parent').find('.color_picker').val();/*Get question's title*/
    title = controller.closest('.parent').find('.title').val();/*Get question's title*/
    content = controller.closest('.parent').find('.mce-edit-area iframe').contents().find('#tinymce').html();/*Get result content*/
    highest = controller.closest('.parent').find('.highest select').val();/*Get result content*/

    options = controller.closest('.parent').find('input.option').map(function(){
      val = $(this).val(); 
      linkid = $(this).closest('.parent').find('.link.main option:selected').attr('data-linkid'); 
      linkpath = $(this).closest('.parent').find('.link.main option:selected').attr('data-linkpath'); 
      id = $(this).closest('.parent').attr('data-option'); 
      score = $(this).closest('.parent').find('.score').val(); 
      imgId = $(this).closest('.parent').find('.image_id').val(); 
      imgUrl = $(this).closest('.parent').find('.uploaded_image').attr('src'); 
      essay_ops = {};
      
      $(this).closest('.parent').find('.essay_answer').each(function(){
        esId = $(this).attr('data-id');
        esVal = $(this).val();
        esSco = $(this).closest('.essay').find('.score').val();
        esLink = $(this).closest('.essay').find('.link.second option:selected').attr('data-linkid');
        esPath = $(this).closest('.essay').find('.link.second option:selected').attr('data-linkpath');
        
        essay_ops[esId] = {
          'id' : esId,
          'value' : esVal,
          'score' : esSco,
          'link' : {
            'linkid' : esLink,
            'linkpath' : esPath,
          },
        };
      });

      options_array = {
        value: val, 
        link: {
          linkid: linkid, 
          linkpath : linkpath
        }, 
        id : id, 
        img : {
          id : imgId, 
          url : imgUrl
        }, 
        score : score, 
        essay_ops : essay_ops
      };

      return options_array;

    }).get();/*Get question options*/
    
    isResult = controller.closest('.parent').hasClass('result');/*Manual / Automatic boolean flag*/
    
    flag = controller.is(':checked');    
    essayFlag = controller.closest('.parent').find('.essay_question_flag').is(':checked');
    multipleFlag = controller.closest('.parent').find('.multiple_choice_flag').is(':checked');

    if (command == 'update_result_criteria_flag') {
      flag = controller.val();
    };

    scoreMin = parseInt(controller.closest('.parent').find('input.range.min').val());
    scoreMax = parseInt(controller.closest('.parent').find('input.range.max').val());


    optionImg = controller.attr('data-img');/*Get option image ID*/
    
    if (image) {
      file = image;
      optionImg = image.id;
    };

    formData = new FormData();

    if (command == 'upload_option_image') {

      filename = 'option_image_'+option;/*Define filename*/

      flag = controller.closest('.parent').find('.option_image_flag').val();

      $.each(file, function(key, value){
        formData.append(filename+'['+key+']', value);
      });

      formData.append('quizu_id', quizuId);
      formData.append('command', command);

      if (command == 'upload_option_image') {
        formData.append('path', path);
        formData.append('option', option);
        prefix = 'option';
      }
      
      formData.append('parent', quparent);
      formData.append('flag', flag);
      formData.append('action', 'quizu_admin_ajax');
      formData.append('_ajax_nonce', quizu_nonce);

    };

  }

  // WP-ADMIN UPDATE HIGHEST SELECTS------------------------------------------------------------------------------------------------------------------

  var resultHighestSelects = function(){
    numberOps = 0;
    counterOps = 0;
    i = 0;

    $('#quizu_questions li.question').each(function(){
      $(this).find('li.option').each(function(){
        counterOps++;

        if (counterOps > numberOps) {
          numberOps = counterOps;
        }

      });
     counterOps = 0;
    });

    $('#quizu_questions div.result').each(function(){

      selected = $(this).find('.highest select').val();

      $(this).find('.highest select option:not([value="option_e"]):not([value=""])').remove();

      while(i < (numberOps - 1)) {
        if (selected == 'option_'+i) {
          $(this).find('.highest select option[value="option_e"]').before('<option selected value="option_'+i+'">'+quizuObj.option+' '+(i + 1)+'</option>');
        }else{
          $(this).find('.highest select option[value="option_e"]').before('<option value="option_'+i+'">'+quizuObj.option+' '+(i + 1)+'</option>');
        }
        i++;
      };

      i = 0;
    });
  }

  // WP-ADMIN NEW QUESTION------------------------------------------------------------------------------------------------------------------

  var toggleControllersSelector = 'select, button, input:not([type="hidden"]), textarea, #publish';
  
  var disableControllers = function(){
    $(toggleControllersSelector).attr('disabled', true);
    $('#quizu_questions .wp-color-result').css({'visibility': 'hidden'});
  }

  var enableControllers = function(){
    $(toggleControllersSelector).removeAttr('disabled');
    $('#quizu_questions .wp-color-result').css({'visibility': 'visible'});
  }

  // CONTROLLER COMMANDS------------------------------------------------------------------------------------------------------------------

  var controllerProcessingSuccess = function(data){

    if (command == 'new_question') {/*Append new question*/
      controller.closest('.path.parent').find('ul.questions.container').prepend(data);
    };

    if ( command == 'new_option') {/*Append new option*/
      controller.closest('.question.parent').find('ul.options.container').append(data);
      resultHighestSelects();
    };

    if ( command == 'new_essay') {/*Append new option*/
      controller.closest('.parent').find('.essay_options').append(data);

      if (controller.closest('.parent').find('.essay_answer').length > 1) {
        controller.closest('.parent').find('.essay_options .remove_essay').last().removeClass('hidden');
      }else{
        controller.closest('.parent').find('.essay_options .remove_essay').last().addClass('hidden');
      }

      controller.closest('.parent').find('.link.second').html(controller.closest('.question.parent').find('.link.main').html());

    };

    if (command == 'new_path') {/*Append new path*/
      exists = controller.parent().find('div.path.parent');/*Check if there are other paths already, and append or preppend accordingly*/
      if (exists.length != 0) {
        exists.first().before(data);
      }else{
        controller.closest('.parent').find('.admin_divider').before(data);
      };
      quizuColorPicker();
      makesort();/*Activate sorting for new questions*/
    };

    questionsList = $('#quizu_questions').find('li.question.parent:first-of-type select:first-of-type').html();

    selectBoxes = $('#quizu_questions').find('select.link');


    if (command == 'new_question' || command == 'new_path') {
      /*When a path or question is created, update select options accordinly*/

      selectBoxes.each(function(){
        optionSelectedLinkid = $(this).find('option:selected').attr('data-linkid');
        selectParent = $(this).closest('.parent').attr('data-question');
        $(this).html(questionsList).find('option').removeClass('hidden');
        $(this).find('option[data-linkid="'+selectParent+'"]').addClass('hidden');
        $(this).find('option[data-linkid="'+optionSelectedLinkid+'"]').attr('selected', 'true');
      });

    };

    if (command == 'new_result') {/*When a result is created, append a representation of it to the select option list*/

      controller.before(data);

      elemId = $(data).find('.wp-editor-area').attr('id');

      tinymce.execCommand('mceAddEditor',false, elemId );

      elem = $('tinymce_'+elemId+'_ifr').find('.wp-editor-area').contents().find('#tinymce');

      $('#quizu_questions').find('select.link').append('<option data-linkpath="'+path+'" data-linkid="'+$(data).find('input.id').val()+'" data-result="true" value="'+$(data).find('input.id').val()+'" data-id="'+option+'">' + 'Res: ' + $(data).find('.title.result').val() + '</option>');

      resultHighestSelects();

    };

    if (
       command == 'delete_question' 
    || command == 'delete_option' 
    || command == 'delete_path' 
    || command == 'delete_result') {
      /*Remove containers on delete*/
      controller.closest('.parent').remove();
    };

    if (
       command == 'delete_question' 
    || command == 'delete_option' 
    || command == 'delete_result'
    ) {
      /*Remove select options on delete*/
      toRemoveQ = controller.closest('.parent').attr('data-question');
      selectBoxes.each(function(){
        $(this).find('option[data-linkid="'+toRemoveQ+'"]').remove();
      });
    };

    if (command == 'delete_essay') {
      contPar = controller.closest('.question.parent');
      controller.closest('.essay').remove();

      if (contPar.find('.essay_answer').length == 1) {
        contPar.find('.remove_essay').addClass('hidden');
      };

      if (quizuObj.flags.autosave == 'true') {
        contPar.find('.controller.update').click();
      };
    };

    if (command == 'delete_path') {
      /*Remove select options on delete*/
      toRemoveP = controller.closest('.parent').attr('data-path');
      selectBoxes.each(function(){
        $(this).find('option[data-linkpath="'+toRemoveP+'"]').remove();
      });
    };

    if (command == 'delete_option') {
      resultHighestSelects();
    };

    if (command == 'delete_option_image') {/*Remove linked option image*/

      controller.closest('.parent').find('.image').removeClass('uploaded');/*Hide delete controller*/
      controller.closest('.parent').find('.uploaded_image').remove();/*Hide delete controller*/
      
    };

    if (
       command == 'update_path' 
    || command == 'update_question' 
    || command == 'update_result'
    ) {
      $('#quizu_questions').find('select.link option').each(function(){
        if (command == 'update_path' && $(this).attr('data-linkpath') == path && $(this).val() != '' ) {
          /*Update select option list*/
          pathTit = $(this).html();
          $(this).text(title.substring(0, 2) + title.slice(-1) + pathTit.substring(pathTit.indexOf(':')));
        };

        if (command == 'update_question' && $(this).val() != '' && $(this).attr('data-linkid') == quparent) {
          /*Update select option list*/
          optionTit = $(this).html();
          $(this).text(optionTit.substring(0, optionTit.indexOf(':')+1) + ' ' + title);
        };

        if (command == 'update_result' && $(this).val() != '' && $(this).attr('data-linkid') == quparent) {
          $(this).text('Res: ' + title);
        };
      }); 
    };
  }

  var controllerProcessingComplete = function(){

    if (command == 'update_question') {
      if (essayFlag == true) {
        controller.closest('.parent').addClass('essay_type');
        controller.closest('.parent').find('.multiple_choice_switch').addClass('hidden');
      }else{
        controller.closest('.parent').removeClass('essay_type');
        controller.closest('.parent').find('.multiple_choice_switch').removeClass('hidden');
      }

      if (multipleFlag == true) {
        controller.closest('.parent').addClass('multiple_choice');
      }else{
        controller.closest('.parent').removeClass('multiple_choice');
      }
    };
  }

  var controllerProcessing = function(control){/*This function handles all SAVE and DELETE operations*/
    
    if (control.type == 'click') {
      controller = $(this);
    }else{
      controller = typeof control !== 'undefined' ? control : $(this);
    };

    getVariables(controller);

    if (command == 'update_question' || command == 'update_result') {
      if (scoreMin > scoreMax) {
        command = 'abort';
      }else{
        $('.result.parent').each(function(key, value){

          rangeMin = $(this).find('.range.min').val();
          rangeMax = $(this).find('.range.max').val();

          if (quparent != $(this).attr('data-question') && scoreMin + scoreMax != 0 && rangeMin + rangeMax != 0) {
            if (Math.max(scoreMin, rangeMin) <= Math.min(scoreMax, rangeMax)) {
              alert(quizuObj.overlap + ' ' +$(this).find('.title.result').val());
              command = 'abort';
            }
          };
        });
      };
    };

    if (quizuObj.flags.autosave != 'true') {

      switch(command) {
          case 'new_path':
              nonSaveElem = 'div.path';
              break;
          case 'new_question':
              nonSaveElem = 'li.question';
              break;
          case 'new_option':
              nonSaveElem = 'li.option';
              break;
          case 'new_result':
              nonSaveElem = 'div.result';
              break;  
          case 'new_essay':
              nonSaveElem = '.essay_answer';
              break;
          default:
              nonSaveElem = null;
      }

      nonSaveItemCount = controller.closest('.parent').find(nonSaveElem).length;

    }else{
      nonSaveItemCount = null;
    };

    if (
      (quizuObj.flags.autosave != 'true' 
      &&  (command == 'delete_question' 
            || command == 'delete_option' 
            || command == 'delete_path' 
            || command == 'delete_result')
          ) 
      || command == 'delete_essay'
    ) {
      data = '';
      controllerProcessingSuccess(data);
      controllerProcessingComplete();
    }else{

      $.ajax({/*Perform ajax requests*/

        url:   ajaxurl,
        data: {
          action : 'quizu_admin_ajax', 
          _ajax_nonce : quizu_nonce,
          quizu_id : quizuId,
          command : command,
          parent : quparent,
          option : option,
          option_img : optionImg, 
          path: path, 
          title: title, 
          content: content, 
          highest: highest, 
          options: options, 
          color : color, 
          flag : flag,
          flags : {
            essay_flag : essayFlag,
            multiple_flag : multipleFlag
          },
          score_min : scoreMin,
          score_max : scoreMax,
          non_save_item_count : nonSaveItemCount,
        },
        type: 'POST',
        dataType: 'html',

        beforeSend: function(xhr){
          disableControllers();
        },
        success: function(data){
          controllerProcessingSuccess(data);
        },
        complete: function(xhr, textStatus){
          enableControllers();
          controllerProcessingComplete();
        }
      });
    }

  }

  // WP-ADMIN SORT QUESTIONS------------------------------------------------------------------------------------------------------------------
  
  var updateQuizTc = function(editor){
    $.ajax({/*Perform ajax requests*/

      url:   ajaxurl,
      data: {
      action : 'quizu_admin_ajax', 
      _ajax_nonce : $('#update_quiz_tc').val(), 
      quizu_id : quizuId, 
      command : 'update_quiz_tc', 
      title : $('#title').val(), 
      content : editor.getContent(), 

      },
      type: 'POST',
      dataType: 'html',

      beforeSend: function(xhr){
        disableControllers();
      },
      success: function(data){
      },
      complete: function(xhr, textStatus){
        enableControllers();
      }
    });
  }

  // WP-ADMIN SORT QUESTIONS------------------------------------------------------------------------------------------------------------------

  var quizuColorPicker = function (){

    $('#quizu_questions .color_picker').wpColorPicker({
      change: function(event, ui){

        if (quizuObj.flags.autosave == 'true') {
          if (typeof colorPickerCounter !== 'undefined') {
            clearTimeout(colorPickerCounter);
          };

          controllerA = $(this);

          colorPickerCounter = setTimeout(function(){
            controllerB = controllerA.closest('.parent').find('.controller.update_path');
            controllerA.closest('.parent').find('.color_picker').attr('value', ui.color);

            controllerProcessing(controllerB);
          }, 1000);
        };

      }
    });
  };

  quizuColorPicker();


  // WP-ADMIN SORT QUESTIONS------------------------------------------------------------------------------------------------------------------
  
  var imageUpload = function(control, event){

    if (typeof event !== 'undefined') {
      event.preventDefault();
    };

    controller = control;

    container = controller.closest('.parent');
    addImgLink = container.find( '.image_upload');
    delImgLink = container.find( '.image_delete');
    imgContainer = container.find( '.image');
    imgIdInput = container.find( '.image_id' );
    imgUrlInput = container.find( '.image_url' );

    if (typeof frame !== 'undefined') {
      frame.open();
      return;
    }

    frame = wp.media({
      title: quizuObj.mediaTitle,
      button: {
        text: quizuObj.mediaText
      },
      multiple: false  // Set to true to allow multiple files to be selected
    });

    frame.on('select', function() {
      // Get media attachment details from the frame state
      attachment = frame.state().get('selection').first().toJSON();

      // Hide the add image link
      imgContainer.addClass('uploaded');
      // Unhide the remove image link
      /*Hide upload image controls*/
      /*Insert image*/
      delImgLink.before('<img class="uploaded_image" src="'+attachment.url+'"></img>');
      /*Create new inputs*/

      imgIdInput.val(attachment.id);
      imgUrlInput.val(attachment.url);

      getVariables(controller, attachment);

      if (quizuObj.flags.autosave == 'true') {
        $.ajax({
          url:   ajaxurl,
          data: formData,
          type: 'POST',
          dataType: 'json',
          processData: false,
          contentType: false,

          beforeSend: function(xhr){
            disableControllers();
          },
          success: function(data){
          },
          complete: function(xhr, textStatus){
            enableControllers();
          }
        });
      };

    });


    // Finally, open the modal on click
    frame.open();
  }


  // WP-ADMIN SORT QUESTIONS------------------------------------------------------------------------------------------------------------------

  makesort = function(){/*Make questions sorting possible*/
    connected = false;
    $('#quizu_questions .questions').sortable({
      // connectWith: ".path .questions.container",
      handle: ".sort_handle",
      receive: function( event, ui ){

        new_path = ui.item.closest('.path.parent').attr('data-path');/*Get receiving quparent path*/
        prev_path = ui.sender.attr('data-path');/*Get previous quparent path*/

        prev_quest = ui.item.attr('data-id');/*Get previous question ID*/

        pathDad = ui.item.closest('.parent.path');
        pathTit = pathDad.find('> wraper > .title.path').val();/*Get quparent path title*/

        ui.item.find('input').each(function(){

          $(this).attr('name', $(this).attr('name').replace(prev_path, new_path));/*Update input names with correct question and path IDs*/

          $('#quizu_questions').each(function(){
            if ($(this).attr('data-linkid') == ui.item.attr('data-id')) {
              $(this).attr('data-linkpath', new_path);
              $(this).html(pathTit + $(this).html().substring($(this).html().indexOf(':')));
            };
          });

        });

        connected = true;

      },
      stop: function(event, ui){
        
       quizu_nonce = ui.item.attr('data-sort');/*Get nonce*/
       command = 'sort_questions';

       path = ui.item.closest('.parent.path').attr('data-path');
       new_order = {};

       ui.item.closest('.path.parent').find('.parent.question').each(function(){/*Make an object containing all IDs in new order*/
         new_order[$(this).attr('data-question')] = '';
       });

       toSend = {action : 'quizu_admin_ajax', _ajax_nonce : quizu_nonce, quizu_id : quizuId, command : command, path : path, new_order : new_order};

       // if (connected == true) {/*Check if sorting in self list, or sorting to other list*/
       //   connectedToSend = {/*Define ajax variables*/
       //     'prev_quest' : prev_quest, 
       //     'new_path' : new_path, 
       //     'prev_path' : prev_path,
       //     connected : connected
       //   };
       //   Object.keys(connectedToSend).forEach(function(item){
       //     toSend[item] = connectedToSend[item];
       //   });
       // }else{
       //   new_path = ui.item.closest('.parent').attr('data-path');
       //   toSend['new_path'] = new_path;
       // };

       // ui.item.attr('data-path', new_path);/*Update new path on item*/

       if (quizuObj.flags.autosave == 'true') {
          $.ajax({/*Update order*/
            url:   ajaxurl,
            data: toSend,
            type: 'POST',
            dataType: 'html',

            beforeSend: function(xhr){
              disableControllers();
            },
            success: function(data){
            },
            complete: function(xhr, textStatus){
              enableControllers();
            },
          });
       };

     }
   });
  }

  makesort();

  $('#quizu_questions').on('click','.controller:not(.flag)', function(event){
    event.preventDefault();
    controllerProcessing($(this));
  });

  if (quizuObj.flags.autosave == "true") {
    // WP-ADMIN SELECT OPTION LINK UPDATES------------------------------------------------------------------------------------------------------------------

    $('#quizu_questions').on('click','.controller.flag', function(event){
      controllerProcessing($(this));
    });

    $('#quizu_questions').on('change keydown', '.title, .essay input, input.score, input.range, .wp-editor-area', function(e){

      if ((e.keyCode == 13 && !$(this).hasClass('title')) || e.type == 'change') {
        if (e.keyCode == 13){
          event.preventDefault();
        }

        if (($(this).hasClass('score') || $(this).hasClass('range')) && isNaN($(this).val())) {
          
            alert(quizuObj.integer);

        }else{
            $(this).closest('.parent:not(.option)').find('.controller.update').click();
        }
      };

    });


    // WP-ADMIN NEXT QUESTION LINK UPDATES------------------------------------------------------------------------------------------------------------------

    $('#quizu_questions').on('change', 'select.link', function(){
      $(this).closest('.parent.question').find('.controller.update').click();
    });

    $('#quizu_questions').on('change', '.highest select', function(){
      $(this).closest('.parent.result').find('.controller.update').click();
    });

    $('#quizu_questions #postdivrich').on('change', '.wp-editor-area', function(){
      $('#publish').find('.controller.update').click();
    });

    if (typeof tinymce !== 'undefined') {
        tinymce.on('SetupEditor', function (editor) {
            if (editor.id === 'content') {
                // Could use new 'input' event instead.
                editor.on('change keyup paste', function (event) {
                    if (typeof mceEditorCounter !== "undefined") {
                      clearTimeout(mceEditorCounter);
                    }

                    mceEditorCounter = setTimeout(function(){
                        jQuery(editor.target.iframeElement).contents().find('span[data-mce-style]').each(function(){
                          jQuery(this).attr('style', jQuery(this).attr('data-mce-style'));
                        });
                        
                        updateQuizTc(editor);

                    }, 500);
                });
            }
        });
    }

    $('#titlewrap').on('change', '#title', function(){
      if ( wp.autosave.server ) {
        updateQuizTc(tinymce.editors['content']);
      }
    });

  }else{
    $('#quizu_questions').on('click','.controller.flag', function(event){
      if ($(this).attr('data-command') == 'update_question') {
        if ($(this).hasClass('essay_question_flag')) {
          if ($(this).is(':checked') == true) {
            $(this).closest('.parent').addClass('essay_type');
          }else{
            $(this).closest('.parent').removeClass('essay_type');
          }
        };

        if ($(this).hasClass('multiple_choice_flag')) {
          if ($(this).is(':checked') == true) {
            $(this).closest('.parent').addClass('multiple_choice');
          }else{
            $(this).closest('.parent').removeClass('multiple_choice');
          }
        };
      };
    });
  }

  // WP-ADMIN SELECT RESULTS CRITERIA------------------------------------------------------------------------------------------------------------------

  $('#quizu_questions').on('change', '#results_criteria', function(){
    $(this).attr('data-flag', $(this).val());

    switch($(this).val()) {

        case 'results_by_total':

          $('#quizu_questions select.link').addClass('results_by_score');
          $('#quizu_questions div.result .buttons').addClass('results_by_total');
          $('#quizu_questions div.result .buttons').removeClass('results_by_option');

          break;

        case 'results_by_option':

          $('#quizu_questions select.link').addClass('results_by_score');
          $('#quizu_questions div.result .buttons').addClass('results_by_option');
          $('#quizu_questions div.result .buttons').removeClass('results_by_total');

          resultHighestSelects();

          break;

        case 'results_by_path':

          $('#quizu_questions select.link').removeClass('results_by_score');
          $('#quizu_questions div.result .buttons').addClass('results_by_total');
          $('#quizu_questions div.result .buttons').removeClass('results_by_option');

          break;

    }

    if (quizuObj.flags.autosave == 'true') {
      controllerProcessing($(this));
    };
  });

  // COLLAPSING

  $('#quizu_questions').on('click', '.collapse, li.collapsed .title, div.collapsed .title', function(event){
    $(this).closest('.parent').toggleClass('collapsed');
    $(this).closest('.parent').find('.title').removeAttr('style').scrollTop(0);
  });

  // OPTION / RESULT IMAGE UPLOAD / DELETE

  $('#quizu_questions').on('click', '.image.upload', function(event){
    imageUpload($(this), event);
  });
});