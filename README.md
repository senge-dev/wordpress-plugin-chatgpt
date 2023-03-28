# 目前程序处于测试阶段，请勿用于生产环境

## 使用教程

1. 申请API Key， 访问： https://platform.openai.com/account/api-keys 申请你的API Key

2. 使用任意身份发送评论，评论内容为： sk-YourApiKey!!评论内容 （!!为两个半角感叹号，视为分隔符，发送后系统会自动修改评论来隐藏你的API Key，程序有一定缺陷，建议测试后销毁你的API Key）

3. 提交评论，此时系统会自动处理你的评论，并将API Key发送到OpenAI官网进行处理

4. ChatBot会调用API来回复你的评论

## 注意

- 测试阶段，请勿用于生产环境

- 可以在服务器端新建一个WordPress站点用于测试，请勿安装在真实的网页环境中

- 程序有一定的缺陷，虽然会自动隐藏API Key，但是为了以防万一，请在测试后销毁API Key。如果你的API Key是通过购买获得的，请勿用于测试

- 目前回复内容暂时存在缺陷
