<div class="YApps_Widget--Callback_Container" role="YApps_Widget" data-appkey="CB">
    <div class="YApps_Widget--Close">Закрыть <svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Close"></use></svg></div>
    <div class="YApps_Widget--Callback_Form">
        <input type="hidden" name="YApps_Widget--Form_Name-Personal" value="Y" />
        <input type="hidden" name="YApps_Widget--Form_Name-Communications" value="Y" />
        <input type="hidden" name="YApps_Widget--Form_Name-Status" value="Now" />
        <div class="YApps_Widget--Form_Title">%%WIDGET.CB.TITLE_PROLOGUE%% <span class="YApps_Widget--Form_Title-Callback_Time">за %%WIDGET.CB.TIMER_AWAIT%% секунд</span></div>
        <div class="YApps_Widget--Form_Text">%%WIDGET.CB.TEXT%%</div>
        <div class="YApps_Widget--Form_Fields">
            <div class="YApps_Widget--Form_Field" id="YApps_Widget--Form_Field-Phone">
                <input inputmode="tel" type="phone" name="YApps_Widget--Form_Name-Phone" placeholder="Телефон" required />
            </div>
            <div class="YApps_Widget--Form_Field" id="YApps_Widget--Form_Field-DateTime">
                <input type="text" name="YApps_Widget--Form_Name-DateTime" placeholder="Дата и время звонка" />
            </div>
            <div class="YApps_Widget--Form_Field" id="YApps_Widget--Form_Field-Send">
                <a href="#" class="YApps_Widget--Form_Button" role="YApps_Widget--Form_Send" data-appkey="CB">%%WIDGET.CB.BUTTON_NOW%%</a>
            </div>
            <div class="YApps_Widget--Form_Fields-Cover"></div>
        </div>
        <div class="YApps_Widget--Form_Result YApps_Widget--Form_success">%%WIDGET.FORM_SUCCESS%%</div>
        <div class="YApps_Widget--Form_Result YApps_Widget--Form_error">%%WIDGET.FORM_ERROR%%</div>
        <div class="YApps_Widget--Callback_Timer">
            <div class="YApps_Widget--Callback_Success">Ожидайте звонка</div>
            <span id="YApps_Widget--Callbac_Seconds">%%WIDGET.CB.TIMER_AWAIT%%</span>.<span id="YApps_Widget--Callbac_mSeconds">0</span>
        </div>
        <div class="YApps_Widget--Form_Callback-Field_Toggle" data-id="YApps_Widget--Form_Field-DateTime" style="%%WIDGET.CB.WORKFLAG%%">%%WIDGET.CB.DESCRIPTION_NOW%%</div>
        %%WIDGET.TERMS%%
    </div>
</div>

