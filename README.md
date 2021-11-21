# org.civicrm.sms.whati


## Overview
This extension provides WhatsApp integration with Whati.

You can only send WhatsApp with this extension and not receive messages.

To create an account, go to "https://www.whati.com/", get token and create own message template. 

## Install the extension
You have to unzip the file and place it in "mywebsite/wp-content/uploads/civicrm/ext".

## Setting up the extension

Administer -> System Settings -> SMS Providers

Add SMS Providers

Select the "Whati" from select field.(required)

In the Title field give any title.

In Username field give just type space. (required)

In Password field give the just type space.(required)

In Api Type select "http".(required)

In API URL field give "https://app-server.wati.io/api/v1/sendTemplateMessage". (required)

In the API PARAMETERS, insert "token=" after equal sign put your own account token provided by http://app-server.wati.io.

![whati provider](assets/sms_provider.png)

Leave the case "Active Provider?" checked.

Leave the case "Is this the default provider?" checked.

## Message body
To integrate with whati templates I included custom template form:

```
[[template_name]]
[[broadcast_name]]
{{par_01===variable 1}}
{{par_02===variable content}}
```
```template_name``` - whati template name (required)

```broadcast_name``` - whati broadcast name(optional)

```par_01``` - parameter name

```===``` - separator between parameter name and variable.(required)

``variable 1`` - content of parameter

For example: 

whati template name: ```woocommerce_default_follow_up_v1```

```
Dear {{name}}, this is {{shop_name}}.

If you have any questions, please feel free to let me know, you can talk to our consultants here on Whatsapp where they will be able to guide you through any requests.
Thanks.
```

bosy of outbound SMS must be:

```
[[woocommerce_default_follow_up_v1]]
{{name===Mr. Simon}}
{{shop_name===Gig market}}
```
and reciver will get:
```
Dear Mr. Simon, this is Gig market.

If you have any questions, please feel free to let me know, you can talk to our consultants here on Whatsapp where they will be able to guide you through any requests.
Thanks.
```
