# LiskDelegateWatcher
<hr/>

<h3>Overview</h3>
<p>
  If you run a Lisk Delegate this may be useful for you. Basically, this script will send you an SMS whenever your delegate misses a block. This way, if your node stops running or something you'll know it instantly, potentially missing less blocks and money than the ones you would without this service. <br/>
  You'll also be notified if your delegate stops being among the top 101 lisk delegates.
 </p>
 
 <h3>Installation and requirements</h3>
 <ul>
  <li>Your system will need to have PHP >= 5.3.0</li>
  <li>Clone this repo where you want to have it</li>
  <li>Install <a href="https://getcomposer.org/" targer="blank">Composer</a> if you don't have it already</li>
  <li>Then run <code>composer require twilio/sdk</code></li>
  <li>And ... done</li>
</ul>

<h3>How to configure and run it</h3>
<p>
  You'll need a twilio account, with a phone number able to send messages to the country you want to send messages. At <code>config.php</code> you can set all the settings to make it work. Each option is greatly explained there :D<br/>
  To run it: <code>php daemon.php</code> <br/>
  You might want to use <code>screen</code> or <code>nohup</code> to send it to the background.
</p>

<h3>Misc</h3>
<p>
  Feel free to suggest changes and updates, to contribute, to test the script, to use it, to whatever ... If you find any issue let me know too and I'll try to fix it. You are more than welcome to fix it yourself and open a pull request :D (hehe) <br/>
  And yeah
</p>
  
