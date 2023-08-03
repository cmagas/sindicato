function paramAuto()
{}

paramAuto.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

paramAuto.prototype.Execute = function()
{
	window.parent.pAutomatico_click();
}

var btnParamAuto=new paramAuto();

FCKCommands.RegisterCommand('btnParamAuto', btnParamAuto);
    // Add the button.
var item = new FCKToolbarButton('btnParamAuto', 'Insertar parámetro automático');
item.IconPath = FCKPlugins.Items['btnParamAuto'].Path + 'PAutomatico.gif';
FCKToolbarItems.RegisterItem('btnParamAuto', item);

