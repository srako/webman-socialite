# webman-socialite

webman-socialite 是一个 [OAuth2](https://oauth.net/2/) 社会化认证工具。


该工具现已支持平台有：Facebook，Github，Google，Linkedin，Outlook，QQ，TAPD，支付宝，淘宝，百度，钉钉，微博，微信，抖音，飞书，Lark，豆瓣，企业微信，腾讯云，Line，Gitee，Coding。



# 安装

```
composer require srako/webman-socialite
```
# 配置
在使用 `Socialite` 之前，您还需要为应用程序使用的 `OAuth` 服务添加凭据。 这些凭证应该放在你的 `config/plugin/srako/socialite/app.php` 配置文件中，并且应该使用密钥 facebook，twitter，linkedin，google，github，gitlab 或 bitbucket， 取决于您的应用程序所需的提供商。 例如：

```php
   'driver' => [
      ...
      'ding-talk' => [
          'client_id' => '',
          'client_secret' => '',
          'redirect' => 'http://your-callback-url',
      ],
      ...
   ]
```

# 参照

- [Alipay - 用户信息授权](https://opendocs.alipay.com/open/289/105656)
- [DingTalk - 获取访问凭证](https://open.dingtalk.com/document/isvapp/obtain-identity-credentials)
- [Google - OpenID Connect](https://developers.google.com/identity/protocols/OpenIDConnect)
- [Github - Authorizing OAuth Apps](https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/)
- [Facebook - Graph API](https://developers.facebook.com/docs/graph-api)
- [Linkedin - Authenticating with OAuth 2.0](https://developer.linkedin.com/docs/oauth2)
- [微博 - OAuth 2.0 授权机制说明](http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E)
- [QQ - OAuth 2.0 登录 QQ](http://wiki.connect.qq.com/oauth2-0%E7%AE%80%E4%BB%8B)
- [腾讯云 - OAuth2.0](https://cloud.tencent.com/document/product/306/37730#.E6.8E.A5.E5.85.A5.E8.85.BE.E8.AE.AF.E4.BA.91-oauth)
- [微信公众平台 - OAuth 文档](http://mp.weixin.qq.com/wiki/9/01f711493b5a02f24b04365ac5d8fd95.html)
- [微信开放平台 - 网站应用微信登录开发指南](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419316505&token=&lang=zh_CN)
- [微信开放平台 - 代公众号发起网页授权](https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&id=open1419318590&token=&lang=zh_CN)
- [企业微信 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90000/90135/91020)
- [企业微信第三方应用 - OAuth 文档](https://open.work.weixin.qq.com/api/doc/90001/90143/91118)
- [豆瓣 - OAuth 2.0 授权机制说明](http://developers.douban.com/wiki/?title=oauth2)
- [抖音 - 网站应用开发指南](http://open.douyin.com/platform/doc)
- [飞书 - 授权说明](https://open.feishu.cn/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Lark - 授权说明](https://open.larksuite.com/document/ukTMukTMukTM/uMTNz4yM1MjLzUzM)
- [Tapd - 用户授权说明](https://www.tapd.cn/help/show#1120003271001000093)
- [Line - OAuth 2.0](https://developers.line.biz/en/docs/line-login/integrate-line-login/)
- [Gitee - OAuth文档](https://gitee.com/api/v5/oauth_doc#/)


# License

MIT
