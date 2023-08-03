/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.ReminderField
 * @extends Ext.form.ComboBox
 * <p>A custom combo used for choosing a reminder setting for an event.</p>
 * <p>This is pretty much a standard combo that is simply pre-configured for the options needed by the
 * calendar components. The default configs are as follows:<pre><code>
    width: 200,
    fieldLabel: 'Reminder',
    mode: 'local',
    triggerAction: 'all',
    forceSelection: true,
    displayField: 'desc',
    valueField: 'value'
</code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.ReminderField = Ext.extend(Ext.form.ComboBox, {
    width: 200,
    fieldLabel: 'Recordar',
    mode: 'local',
    triggerAction: 'all',
    forceSelection: true,
    displayField: 'desc',
    valueField: 'value',

    // private
    initComponent: function() {
        Ext.calendar.ReminderField.superclass.initComponent.call(this);

        this.store = this.store || new Ext.data.ArrayStore({
            fields: ['value', 'desc'],
            idIndex: 0,
            data: [
            ['', 'No'],
            ['0', 'Al inciar'],
            ['5', '5 min&uacute;tos antes de iniciar'],
            ['15', '15 min&uacute;tos antes de iniciar'],
            ['30', '30 min&uacute;tos antes de iniciar'],
            ['60', '1 hora antes de iniciar'],
            ['90', '1.5 horas antes de iniciar'],
            ['120', '2 hora antes de iniciar'],
            ['180', '3 hora antes de iniciar'],
            ['360', '6 hora antes de iniciar'],
            ['720', '12 hora antes de iniciar'],
            ['1440', '1 d&iacute;a antes de iniciar'],
            ['2880', '2 d&iacute;as antes de iniciar'],
            ['4320', '3 d&iacute;as antes de iniciar'],
            ['5760', '4 d&iacute;as antes de iniciar'],
            ['7200', '5 d&iacute;as antes de iniciar'],
            ['10080', '1 semana antes de iniciar'],
            ['20160', '2 semanas antes de iniciar']
            ]
        });
    },

    // inherited docs
    initValue: function() {
        if (this.value !== undefined) {
            this.setValue(this.value);
        }
        else {
            this.setValue('');
        }
        this.originalValue = this.getValue();
    }
});

Ext.reg('reminderfield', Ext.calendar.ReminderField);
