jQuery(document).ready(function(e){var s=function(e){quizuElem=e,quizuId=quizuElem.attr("data-id"),quizuBase=quizuObj.base,quizPaths=quizuBase,selQuestion={},optIds={},selResult={},selResult.scoreTrayectory={},quizResults={},quizProgressBase={},summScores={},summScores.total={},summScores.highest={},currentQuestion=quizuElem.find(".next").attr("data-current"),currentPath=quizuElem.find(".next").attr("data-path"),void 0!==localStorage.quizuQuizesResults&&(quizResults=JSON.parse(localStorage.quizuQuizesResults),quizuId in quizResults&&(selResult=quizResults[quizuId]))},t=function(){sessionStorage.removeItem("quizuQuizProgress"),u();var s=e('<button class="reload" style="background-color: '+quizuObj.defaultColor+';">'+quizuObj.reset+"</button>");quizuElem.append(s),quizuElem.find(".question").html(quizuObj.error)},u=function(){quizuElem.find(".reset").remove(),quizuElem.removeClass("multiple_choice"),quizuElem.removeClass("essay_type"),quizuElem.find(".result").remove(),quizuElem.find(".jssocials").remove(),quizuElem.find(".option_next").remove(),quizuElem.find(".open_answer").remove(),quizuElem.find(".score").remove(),quizuElem.find(".option_score").remove(),quizuElem.find(".email").remove()},o=function(){isResult=!1,linkId=quizuElem.find(".option_next.checked").attr("data-linkid"),linkPath=quizuElem.find(".option_next.checked").attr("data-linkpath"),"undefined"!=typeof goFinalize&&1==goFinalize&&(linkId="finalize"),"undefined"!=typeof questType&&"multiple"==questType&&(linkId=quizuElem.find(".option_next:first").attr("data-linkid"),linkPath=quizuElem.find(".option_next:first").attr("data-linkpath"),quizuElem.find(".option_next.checked").each(function(){optIds[e(this).attr("data-id")]=e(this).attr("data-id")})),"true"==quizPaths.paths[currentPath].questions[currentQuestion].flags.essay_flag&&(essay_options=quizPaths.paths[currentPath].questions[currentQuestion].options.essay.essay_ops,chosen_essay=quizuElem.find(".open_answer").val(),e.each(essay_options,function(e,s){s.value.toLowerCase()==chosen_essay.toLowerCase()&&s.link&&(linkId=s.link.linkid,linkPath=s.link.linkpath)})),("results_by_total"==quizPaths.resultsCriteriaFlag||"results_by_option"==quizPaths.resultsCriteriaFlag)&&linkId in quizPaths.results&&(linkId="finalize"),linkId in quizPaths.results&&(isResult=!0,selResult=quizPaths.results[linkId],selResult||(selResult=quizPaths.results[quizResults[quizuId].id])),!isResult&&linkPath in quizPaths.paths&&linkId in quizPaths.paths[linkPath].questions?selQuestion=quizPaths.paths[linkPath].questions[linkId]:(isResult||selQuestion||t(),selQuestion=!1),"finalize"==linkId?(isResult=!0,l(),"results_by_total"==quizPaths.resultsCriteriaFlag&&e.each(quizPaths.results,function(s,t){if(parseInt(t.score.min)<=parseInt(quizProgress.summScores.total)&&parseInt(quizProgress.summScores.total)<=parseInt(t.score.max))return e.extend(selResult,t),!1}),"results_by_option"==quizPaths.resultsCriteriaFlag&&e.each(quizPaths.results,function(s,t){if(t.highest==quizProgress.summScores.highest)return e.extend(selResult,t),!1}),selResult.id||e.extend(selResult,quizPaths.results[Object.keys(quizPaths.results)[0]])):l(),isResult?void 0===selResult.id?t():a():selQuestion?n():(r(),linkPath==currentPath&&selQuestion.id==quizPaths.paths[currentPath].questions[Object.keys(quizPaths.paths[currentPath].questions)[0]].id?"results_by_total"==quizPaths.resultsCriteriaFlag||"results_by_option"==quizPaths.resultsCriteriaFlag?(quizProgressBaseB=JSON.parse(sessionStorage.quizuQuizProgress),delete quizProgressBaseB[quizuId].questions[Object.keys(quizProgress.questions)[Object.keys(quizProgress.questions).length-1]],sessionStorage.setItem("quizuQuizProgress",JSON.stringify(quizProgressBaseB)),goFinalize=!0,o(),delete goFinalize):(selResult=quizPaths.results[Object.keys(quizPaths.results)[0]],selResult?(selResult.scoreTrayectory=quizProgress,a()):t()):n())},r=function(){quizPaths&&(currentQuestion=quizuElem.find(".next").attr("data-current"),currentPath=quizuElem.find(".next").attr("data-path"),quizuQuest=quizPaths.paths[currentPath].questions,questionFound=!1,e.each(quizuQuest,function(e,s){if(selQuestion=s,1==questionFound)return!1;s.id==currentQuestion&&(questionFound=!0)}),pathFound=!1,selQuestion.id==currentQuestion&&e.each(quizPaths.paths,function(e,s){if(linkPath=s.id,selQuestion=s.questions[Object.keys(s.questions)[0]],1==pathFound)return!1;s.id==currentPath&&(pathFound=!0)}))},n=function(){linkPath&&"undefined"!=linkPath&&"undefined"!=typeof linkPath||(linkPath=currentPath),currentColor=quizPaths.paths[linkPath].color,u(),"true"==selQuestion.flags.multiple_choice_flag?quizuElem.addClass("multiple_choice"):quizuElem.removeClass("multiple_choice"),"true"==selQuestion.flags.essay_flag?quizuElem.addClass("essay_type"):quizuElem.removeClass("essay_type"),quizuElem.find(".question").html(selQuestion.title.nl2br()),e.each(selQuestion.options,function(s,t){if("true"==selQuestion.flags.essay_flag&&"essay"!=t.id)return!0;if("true"!=selQuestion.flags.essay_flag&&"essay"==t.id)return!0;var i,u='<p style="background-color: '+currentColor+';">'+t.value+"</p>",o=e("<div></div>").attr({class:"option_next","data-id":"essay","data-result":"false",value:quizuObj.next,"data-linkid":t.link.linkid,"data-linkpath":""==t.link.linkpath?quizuElem.find(".next").attr("data-path"):t.link.linkpath});if("essay"==t.id?(o.attr("data-id","essay"),i='<input type="text" class="open_answer" placeholder="'+t.value+'">',u='<p style="background-color: '+currentColor+';">'+quizuObj.next+"</p>"):o.attr("data-id",t.id),t.img.url)var r=e('<img class="option_image" src="'+t.img.url+'" height="50" width="50">');o.append(u).append(r),quizuElem.find(".next").before(o),quizuElem.find(".option_next").before(i),quizuElem.find(".open_answer").length>0&&quizuElem.find(".open_answer").first().focus()}),quizuElem.find(".next").attr("data-current",selQuestion.id),quizuElem.find(".next").attr("data-path",""==linkPath||"undefined"==linkPath||"undefined"==typeof linkPath?quizuElem.find(".next").attr("data-path"):linkPath),quizuElem.find(".next").css({"background-color":currentColor}),quizuElem.find(".next").html(quizuObj.next)},a=function(){u(),quizuElem.find(".question").html(selResult.title.nl2br()),"<p><br></p>"==selResult.content||""==selResult.content?(selResult.content="",quizuElem.append('<div class="result no-margin">'+selResult.content+"</div>")):quizuElem.append('<div class="result">'+selResult.content+"</div>"),toInsertR=!0,c(),toInsertR=!1;var s=e('<button class="reset" style="background-color: '+quizuObj.defaultColor+';">'+quizuObj.reset+"</button>");if(quizuElem.append(s),"true"==quizPaths.showScoresFlag&&(quizuElem.find(".result").after('<p class="score">'+quizuObj.totalScore+"<strong> "+selResult.scoreTrayectory.summScores.total+"</strong></p>"),"results_by_option"==quizPaths.resultsCriteriaFlag&&(i=1,sortedKeys=Object.keys(selResult.scoreTrayectory.summScores.options).sort(),e.each(sortedKeys,function(e,s){"option_e"==s?quizuElem.find(".reset").before('<p class="option_score">'+quizuObj.optionEssay+" = <strong>"+selResult.scoreTrayectory.summScores.options[s]+"</strong></p>"):(quizuElem.find(".reset").before('<p class="option_score">'+quizuObj.option+" "+(parseInt(s.substr(s.length-1))+1)+" = <strong>"+selResult.scoreTrayectory.summScores.options[s]+"</strong></p>"),i++)}),i=0)),"true"==quizuObj.flags.socialSharingFlag){quizuElem.find(".reset").before('<div class="jssocials"></div>'),quizuElem.find(".reset").after('<button class="email input">'+quizuObj.email+"</div>");var t=["googleplus","facebook","twitter"];/Mobi/.test(navigator.userAgent)&&(t.push("email"),t.push({share:"whatsapp",logo:"fa fa-phone"})),"undefined"!=typeof jsSocials&&quizuElem.find(".jssocials").jsSocials({url:window.location.href,shares:t,showCount:!1,showLabel:!1,shareIn:"popup",text:quizuObj.share+"\n\n"})}quizResults[quizuId]=selResult,localStorage.setItem("quizuQuizesResults",JSON.stringify(quizResults))},l=function(){quizProgress={},quizProgress.questions={},quizProgress.summScores={},quizProgress.summScores.options={},sessionStorage.getItem("quizuQuizProgress")&&(quizProgressBase=JSON.parse(sessionStorage.getItem("quizuQuizProgress")),quizProgressBase[quizuId]&&(quizProgress=e.extend(!0,{},quizProgressBase[quizuId]))),progressCurrent=quizuElem.find(".next").attr("data-current"),progressPath=quizuElem.find(".next").attr("data-path"),progressCount=0,progressCurrentOptions=e.extend(!0,{},quizPaths.paths[progressPath].questions[progressCurrent].options),progressOptionCount=0,progressCount=Object.keys(quizProgress.questions).length,quizProgress.questions["question_"+progressCount]={},quizProgress.questions["question_"+progressCount].option={},quizProgress.questions["question_"+progressCount].id=progressCurrent,e.each(progressCurrentOptions,function(e,s){s.id in optIds&&(progressOptionFound=s,optionCounted=progressOptionCount,"essay"==progressOptionFound.id&&(optionCounted="e"),quizProgress.questions["question_"+progressCount].option[s.id]={},quizProgress.questions["question_"+progressCount].option[s.id]=progressOptionFound,quizProgress.questions["question_"+progressCount].option[s.id].place=optionCounted),"essay"!=s.id&&progressOptionCount++}),progressOptionCount=0,progressCount=0,quizProgress.summScores.total=0,e.each(quizProgress.summScores.options,function(e,s){quizProgress.summScores.options[e]=0}),quizProgress.summScores||(quizProgress.summScores.highest="option_0"),e.each(quizProgress.questions,function(s,t){e.each(t.option,function(i,u){quizProgress.summScores.options["option_"+u.place]||(quizProgress.summScores.options["option_"+u.place]=0),"essay"==u.id?e.each(u.essay_ops,function(e,o){quPlace=parseInt(s.split("_").pop()),quLength=Object.keys(quizProgress.questions).length-1,correctAnswer=!1,openAnswer.toLowerCase()==o.value.toLowerCase()&&quPlace==quLength&&t.id==currentQuestion&&(quizProgress.questions[s].option[i].essay_ops[e].correct=!0,correctAnswer=!0),1==o.correct&&(correctAnswer=!0),1==correctAnswer&&(quizProgress.summScores.options["option_"+u.place]+=parseInt(o.score),quizProgress.summScores.total+=parseInt(o.score))}):(quizProgress.summScores.options["option_"+u.place]+=parseInt(u.score),quizProgress.summScores.total+=parseInt(u.score))})}),openAnswer="",sortedKeys=Object.keys(quizProgress.summScores.options).sort(),e.each(sortedKeys,function(e,s){parseInt(quizProgress.summScores.options[s])>=(void 0!==quizProgress.summScores.options[quizProgress.summScores.highest]?quizProgress.summScores.options[quizProgress.summScores.highest]:0)&&(quizProgress.summScores.highest=s)}),quizProgressBase[quizuId]=quizProgress,isResult&&(selResult.scoreTrayectory=quizProgress,delete quizProgressBase[quizuId]),sessionStorage.setItem("quizuQuizProgress",JSON.stringify(quizProgressBase))},c=function(){title=quizuElem.find(".question").text(),result=quizuElem.find(".result").text(),quiz=quizuElem.find("h3").text(),email=quizuElem.find(".email.address").change().val(),"undefined"!=typeof email&&email!=quizuObj.userEmail&&(quizuObj.userEmail=email);var s={"{{user-email}}":quizuObj.userEmail,"{{result-title}}":title,"{{result-content}}":result};e.each(quizuObjOrT,function(t,i){e.each(s,function(e,s){i=i.split(e).join(s),quizuObj[t]=i})})};String.prototype.nl2br=function(){return this.replace(/\n/g,"<br />")},e(window).on("unload",function(){sessionStorage.removeItem("quizuQuizProgress")}),e(".quizu_widget").each(function(){s(e(this)),c(),quizuId in quizResults&&a(),quizuElem.addClass("rendered"),quizuElem.on("click",".option_next",function(t){if(t.preventDefault(),s(e(this).closest(".quizu_widget")),"essay"==e(this).attr("data-id")){if(!quizuElem.find(".open_answer").change().val())return void alert(quizuObj.essayError);openAnswer=e(this).closest(".quizu_widget").find(".open_answer").val()}e(this).hasClass("checked")?(currentColor=e(this).find("p").css("outlineColor"),props={"background-color":currentColor,color:""},e(this).removeClass("checked"),e(this).find("p").css(props)):(currentColor=e(this).find("p").css("backgroundColor"),props={color:currentColor,"box-shadow":"inset 0px 0px 0px 2px "+currentColor,"background-color":""},e(this).addClass("checked"),e(this).find("p").css(props)),optIds[e(this).attr("data-id")]=e(this).attr("data-id"),("true"!=quizPaths.paths[currentPath].questions[currentQuestion].flags.multiple_choice_flag||"true"==quizPaths.paths[currentPath].questions[currentQuestion].flags.multiple_choice_flag&&"true"==quizPaths.paths[currentPath].questions[currentQuestion].flags.essay_flag)&&o()}),quizuElem.on("click",".next",function(t){t.preventDefault(),0!=e(this).closest(".quizu_widget").find(".option_next.checked").length?(s(e(this).closest(".quizu_widget")),"true"==quizPaths.paths[currentPath].questions[currentQuestion].flags.multiple_choice_flag&&(questType="multiple",o(),delete questType)):alert(quizuObj.checkedError)}),quizuElem.on("keypress",".open_answer",function(t){13===t.which&&(s(e(this).closest(".quizu_widget")),quizuElem.find(".option_next").click())}),quizuElem.on("keypress",".email.address",function(t){13===t.which&&(s(e(this).closest(".quizu_widget")),quizuElem.find(".send.email").click())}),quizuElem.on("click",".email.input",function(t){s(e(this).closest(".quizu_widget")),e(this).removeClass("input").addClass("send"),e(this).text(quizuObj.send),e(this).after('<input type="email" class="email address" placeholder="john-doe@example.com" value="'+quizuObj.userEmail+'" autocomplete="on">')}),quizuElem.on("click",".send.email",function(t){s(e(this).closest(".quizu_widget")),quizuElem.find(".send.email").attr("disabled","true");var i={};i.main={},i.options={},i.main=quizuElem.find(".score").html(),quizuElem.find(".option_score").each(function(s){i.options[s]=e(this).html()}),emailNonce=quizuElem.attr("data-email"),c(),that=e(this),e.ajax({url:quizuObj.ajaxurl,data:{action:"quizu_front_ajax",_ajax_nonce:emailNonce,command:"email",email:email,title:title,subject:quizuObj.emailSubject,message:quizuObj.emailMessage,content:result,quiz:quiz,scores:i,quizu_id:quizuId},type:"POST",dataType:"json",beforeSend:function(e){that.html('<i class="fa fa-spinner fa-spin"></i>')},success:function(e){1==e.success&&(quizuElem.find(".send.email").remove(),quizuElem.find(".email.address").remove(),quizuElem.find(".email.error").remove(),quizuElem.find(".reset").after('<p class="post email">'+quizuObj.postEmail+"</p>")),0==e.success&&(that.html(quizuObj.send),that.removeAttr("disabled"),quizuElem.find(".email.error").remove(),that.after('<p class="email error">'+quizuObj.emailError+"</p>"))},complete:function(e,s){}})}),quizuElem.on("click",".reset",function(t){t.preventDefault(),s(e(this).closest(".quizu_widget"));var i=quizPaths.paths.default.questions;for(key in i){selQuestion=quizPaths.paths.default.questions[key];break}u(),linkPath="default",n(),delete quizResults[quizuId],localStorage.setItem("quizuQuizesResults",JSON.stringify(quizResults))}),quizuElem.on("click",".reload",function(e){e.preventDefault(),delete quizResults[quizuId],localStorage.setItem("quizuQuizesResults",JSON.stringify(quizResults)),location.reload()})})});