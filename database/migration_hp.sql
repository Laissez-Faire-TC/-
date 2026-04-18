-- HP コンテンツ管理テーブル
CREATE TABLE IF NOT EXISTS `hp_news` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `news_date` VARCHAR(50) NOT NULL DEFAULT '',
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `description` TEXT,
  `image_path` VARCHAR(500),
  `anchor_id` VARCHAR(100),
  `is_quick_news` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hp_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `month_key` VARCHAR(20) NOT NULL,
  `month_label` VARCHAR(10) NOT NULL,
  `month_en` VARCHAR(20) NOT NULL,
  `title` VARCHAR(200) NOT NULL DEFAULT '',
  `text_html` MEDIUMTEXT,
  `extra_html` TEXT,
  `images` JSON,
  `type` VARCHAR(50) NOT NULL DEFAULT 'normal',
  `sort_order` INT NOT NULL DEFAULT 0,
  UNIQUE KEY `uk_month_key` (`month_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `hp_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` MEDIUMTEXT,
  UNIQUE KEY `uk_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 初期設定データ
INSERT IGNORE INTO `hp_settings` (`setting_key`, `setting_value`) VALUES
('about_description', '<p>Laissez-Faire T.C.(レッセフェール、通称レッセ)は<br><strong>オール早稲田理工・飲酒禁止・初心者歓迎</strong>のテニスサークルです！</p>\n<p>初心者から経験者まで同じコートで本気になれる雰囲気と、学年・性別関係なく仲が良いアットホームさが自慢です。</p>\n<p>季節ごとのイベントや企画も充実していて<br>テニスも大学生活も全力で楽しみたい理工生にぴったりの環境が整っています。<br>活動場所は理工キャンパスの最寄駅から一本でアクセスできるため、通いやすさも魅力のひとつです！</p>\n<p>楽しさも成長も欲張りたい方、ぜひレッセにお越しください！</p>'),
('about_info', '[{"th":"人数","td":"約200名（オール早稲田理工）"},{"th":"男女比","td":"男 2 : 女 1"},{"th":"活動場所","td":"城北中央公園（3～11月）<br>木場公園・光が丘公園（12～2月）"},{"th":"活動頻度","td":"週3日（月曜・金曜 ＋ 火曜or水曜）"},{"th":"活動時間","td":"19:00～21:00<br>(5限後に来る人もいます！)"}]'),
('about_achievements', '[{"event":"新早連（学内団体戦）","result":"1部昇格"},{"event":"エスパジオ（団体戦）","result":"ベスト4"}]'),
('contact_instagram', 'https://www.instagram.com/laissezfairetc_2026?igsh=amthYmhyNXVkZzNn'),
('contact_twitter', 'https://twitter.com/@laissez_2026'),
('quick_news', '[{"text":"2026.02.05 - スキー合宿報告を公開しました！","anchor":"news-ski"}]');

-- 初期ニュースデータ
INSERT IGNORE INTO `hp_news` (`news_date`, `title`, `description`, `image_path`, `anchor_id`, `is_quick_news`, `sort_order`) VALUES
('2026.02.05 - 02.06', 'スキー合宿2026！', '2/5(木)~2/6(金)に1泊2日でスキー合宿に行ってきました。尾瀬岩鞍スキー場は2日間とも快晴で気持ちよく滑ることができました！', '/public/images/2026スキー.jpg', 'news-ski', 1, 0);

-- 初期スケジュールデータ
INSERT IGNORE INTO `hp_schedule` (`month_key`, `month_label`, `month_en`, `title`, `text_html`, `extra_html`, `images`, `type`, `sort_order`) VALUES
('april', '4月', 'APRIL', '新入生歓迎会',
'<p>新学期の始まりは、Laissez-Faire最大のイベント「新歓」から始まります。早稲田理工公認サークルとして、毎年多くの新入生を迎えています。</p>\n<p>初心者の方も大歓迎です！先輩たちがマンツーマンで教えるので、その日のうちにラリーができるようになります。ラケットの貸出もあるので手ぶらでOK！</p>\n<p>テニスの後は、理工学部の履修相談や学生生活のアドバイスも行っています。一人での参加も多いので、気軽に来てください！</p>',
'<b>Q. 初心者でも大丈夫？</b><p>現メンバーの約半数が大学からテニスを始めています！</p>\n<b>Q. 理工学部以外は？</b><p>レッセは「オール早稲田理工」限定なので、同じキャンパスの仲間が作れます。</p>',
'["/public/images/4月/新歓1.jpg","/public/images/4月/新歓2.jpg","/public/images/4月/新歓3.jpg"]',
'normal', 1),

('may', '5月', 'MAY', 'ラケットツアー',
'<p>自分に合ったラケットやシューズ、その他必要なテニスグッズを先輩と一緒に選びに行きます。毎年ウインザーラケットショップ渋谷店さんにお世話になっており、店員の方にラケット選びの相談に乗ってもらえます。道具が揃うとモチベーションも一気に上がります！</p>',
'', '[]', 'normal', 2),

('june', '6月', 'JUNE', '総会',
'<p>入会が決まった新入生のための公式な親睦会です！本格的にレッセの一員として活動が始まる記念すべき日です。</p>\n<p>同期や先輩たちとじっくり話せる機会なので、ここで一気に仲が深まります！</p>',
'', '["/public/images/6月/総会.jpg"]', 'normal', 3),

('july', '7月', 'JULY', '前期試験期間（テスト）',
'<p>理工学部の山場！この期間は練習をお休みして、みんなで西早稲田キャンパスにこもって勉強します。先輩や同期との助け合いも盛んです！</p>',
'', '[]', 'study', 4),

('august', '8月', 'AUGUST', '夏企画',
'<p>夏休み本番！この時期はテニスの練習だけでなく、夏休みの期間を活かして様々なイベントを企画しています！去年の夏企画では、班ごとに分かれて「料理対決」を開催しました。</p>\n<p>買い出しから調理まで、班のメンバーで協力して一つのメニューを作り上げます。どの班が一番美味しく作れるか競い、賑やかな時間になりました！</p>',
'', '["/public/images/8月/料理1.jpg","/public/images/8月/S__64077854_0.jpg","/public/images/8月/S__64077855_0.jpg"]', 'normal', 5),

('september', '9月', 'SEPTEMBER', '夏合宿',
'<p>レッセ最大のイベント、3泊4日の「夏合宿」！テニスはもちろん、それ以外の企画も本気で楽しみます！</p>\n<p><b>【紅白戦】</b>紅組と白組に分かれてダブルスの団体戦！勝利チームには豪華景品も。応援にも熱が入り、サークルの一体感が一気に高まります。</p>\n<p><b>【夜企画】</b>夜のメイン企画も盛りだくさん。夜道に刺客が現れる本格的な「肝試し」や、体育館での「気配切り」、大広間での「クイズ大会」や「利きポテチ・利きお茶」など、いろんな企画で仲良くなること間違いなし！</p>\n<p><b>【引退式】</b>最終日には、これまでサークルを支えてくれた先輩たちの引退式が行われます。</p>\n<p>他にもみんなで花火をしたり、朝日を見に行ったり。この4日間を共に過ごせば、同期や先輩との絆は一生モノになります！</p>',
'', '["/public/images/9月/S__64086062_0.jpg","/public/images/9月/S__64086063_0.jpg","/public/images/9月/S__64086064_0.jpg"]', 'normal', 6),

('october', '10月', 'OCTOBER', 'ハロウィン',
'<p>ハロウィンが近づくと、いつもの練習も「特別バージョン」に！みんな思い思いの仮装でコートに集まり、いつもとはひと味違う雰囲気でテニスを楽しみます。✨</p>\n<p>しかも、仮装して参加するとお菓子がもらえちゃう嬉しい特典付き！🍭</p>\n<p>目玉はなんといっても「仮装コンテスト」。昨年の優勝は、2年生女子がプロデュースした2年生男子のメイド！みんなクオリティが非常に高く、サークル全体が大盛り上がりするレッセ恒例のイベントです。🎉</p>',
'', '["/public/images/10月/S__64086070_0.jpg","/public/images/10月/S__64086071_0.jpg"]', 'normal', 7),

('november', '11月', 'NOVEMBER', 'エスパジオ (Espajio)',
'<p>エスパジオとは、年3回（6月〜7月、11月、3月）開催される、初心者から経験者まで全員が楽しめる大規模なテニス大会です！✨</p>\n<p>他大学のテニスサークルと対戦し、見事優勝すると豪華景品がもらえるチャンスも！🎁 レッセの代表としてチーム一丸となって勝利を目指します。</p>\n<p>2泊3日で行われるこの大会は、試合はもちろん、一緒に泊まって過ごすことでサークルの仲間やチームメンバーとの絆が深まること間違いなし！</p>',
'', '["/public/images/11月/S__64086074_0.jpg","/public/images/11月/S__64086075_0.jpg","/public/images/11月/S__64086076_0.jpg"]', 'espazio', 8),

('december', '12月', 'DECEMBER', 'クリスマスパーティー（クリパ）',
'<p>冬のビッグイベント「クリスマスパーティー（通称：クリパ）」をご紹介します！テニスコートを離れ、都内の会場を貸し切って行われる華やかなパーティーです。</p>\n<p><b>● ドレスアップ</b>：この日はドレスコードがあり、男子はビシッとスーツを、女子は華やかなドレスを身にまといます。普段の練習着姿とは全く違う、みんなの大人っぽい一面が見られるのもクリパの醍醐味です。</p>\n<p><b>● 豪華ビュッフェ</b>：会場では豪華なビュッフェ料理が振る舞われます。美味しい食事を楽しみながら、学年や学科の垣根を超えてゆったりと交流を深めることができます。</p>\n<p><b>● ビンゴ大会・ギフト交換</b>：毎年恒例のビンゴ大会では、豪華景品をかけて大盛り上がり！サークル全体が温かいクリスマスムードに包まれます。</p>\n<p>一年を締めくくる最高の夜を、最高の仲間たちとドレスアップして楽しみましょう！</p>',
'', '["/public/images/12月/S__64086079_0.jpg","/public/images/12月/S__64086080_0.jpg","/public/images/12月/S__64086081_0.jpg"]', 'normal', 9),

('january', '1月', 'JANUARY', '後期試験期間（テスト）',
'<p>後期のテストも一丸となって乗り切ります！ここを耐えれば、待ちに待った春のスキー合宿が待っています。</p>',
'', '[]', 'study', 10),

('february', '2月', 'FEBRUARY', 'スキー・スノボ合宿',
'<p>冬は雪山へ！テニスとはまた違う楽しさがあり、初心者の子も先輩がしっかり教えてくれるので滑れるようになります。</p>',
'', '["/public/images/2月/S__64086086_0.jpg","/public/images/2月/S__64086087_0.jpg"]', 'normal', 11),

('march', '3月', 'MARCH', '春合宿',
'<p>千葉県白子町にて3泊4日で行われる「春合宿」をご紹介します。1年間の締めくくりとして、テニスも遊びも全力で取り組む合宿です。🌸</p>\n<p><b>● 団体戦</b>：複数のチームに分かれてトーナメント戦を行います。上位チームには豪華景品が贈られるため、非常に盛り上がる企画です。🏆</p>\n<p><b>● 紅白戦</b>：全40試合にも及ぶガチンコの対抗戦です。自軍の応援にも熱が入り、勝利チームには「お菓子の掴み取り」などの嬉しい特典も用意されています。🎾</p>\n<p><b>● 夜レク企画</b>：夜には室内でのレクリエーションが行われます。チームで団結してクイズの解答を考えたり、テニスコートとは違う雰囲気で学年を超えた交流を楽しみます。🧠</p>\n<p>テニスはもちろん、遊びにも一生懸命なレッセの魅力を存分に味わえる4日間です。</p>',
'', '["/public/images/3月/S__64086091_0.jpg","/public/images/3月/S__64086092_0.jpg","/public/images/3月/S__64086093_0.jpg"]', 'normal', 12);
