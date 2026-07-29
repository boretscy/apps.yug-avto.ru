<div class="YApps_Widget--Quiz_Container" role="YApps_Widget" data-appkey="QZ">
  <div class="YApps_Widget--Close">Закрыть <svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Close"></use></svg></div>
  
  %%WIDGET.QZ.SLIDES%%
  
  <div class="YApps_Widget--Quiz_Content" data-step="%%WIDGET.QZ.LAST_STEP%%">
    <div class="YApps_Widget--Quiz_Title">
      <svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_QuizButton"></use></svg>
      %%WIDGET.QZ.LAST_TITLE%%
    </div>
    <hr class="YApps--HR" />
      
      <div class="YApps_Widget--Quiz_Text-Title">%%WIDGET.QZ.LAST_BIGTEXT%%</div>
      <div class="YApps_Widget--Quiz_Text">%%WIDGET.QZ.LAST_TEXT%%</div>
      
      <div class="YApps_Widget--Form_Fields">
        <div class="YApps_Widget--Form_Field YApps_Widget--Width50" id="YApps_Widget--Form_Field-Name">
          <input type="text" name="YApps_Widget--Form_Name-Name" placeholder="Ваше имя *" required="">
        </div>
        <div class="YApps_Widget--Form_Field YApps_Widget--Width50" id="YApps_Widget--Form_Field-Phone">
          <input inputmode="tel" type="phone" name="YApps_Widget--Form_Name-Phone" placeholder="Телефон *" required="">
        </div>
        <div class="YApps_Widget--Form_Field YApps_Widget--Width50" id="YApps_Widget--Form_Field-Email">
          <input inputmode="email" name="YApps_Widget--Form_Name-Email" placeholder="Email">
        </div>
        <div class="YApps_Widget--Form_Field YApps_Widget--Width50" id="YApps_Widget--Form_Field-Send">
          <a href="#" class="YApps_Widget--Form_Button" role="YApps_Widget--Form_Send" data-appkey="QZ">%%WIDGET.QZ.BUTTON_TEXT%%</a>
        </div>
        <div class="YApps_Widget--Form_Text"><small>* - обязательно для заполнения</small></div>
        <div class="YApps_Widget--Form_Fields-Cover"></div>
      </div>
      <div class="YApps_Widget--Form_Result YApps_Widget--Form_success">%%WIDGET.FORM_SUCCESS%%</div>
      <div class="YApps_Widget--Form_Result YApps_Widget--Form_error">%%WIDGET.FORM_ERROR%%</div>
      %%WIDGET.TERMS%%
  </div>
  
  <hr class="YApps--HR" />
  <div class="YApps_Widget--Quiz_Footer">
    
    <div class="YApps_Widget--Quiz_Footer-Parts YApps_Widget--Quiz_Footer-Parts_Left">
      <span class="YApps_Widget--Quiz_Button PrevButton" role="YApps_Widget--Quiz-PrevStep">
        <svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_ArrowLeft"></use></svg>
      </span>
    </div>
    <div class="YApps_Widget--Quiz_Footer-Parts YApps_Widget--Quiz_Footer-Parts_Center">
    %%WIDGET.QZ.PAGINATION%%
    </div>
    <div class="YApps_Widget--Quiz_Footer-Parts YApps_Widget--Quiz_Footer-Parts_Right">
      <span class="YApps_Widget--Quiz_Button YApps_Widget--Quiz_Button-DeActive" role="YApps_Widget--Quiz-NextStep">
        <svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_ArrowRight"></use></svg>
      </span>
    </div>
  </div>
</div>