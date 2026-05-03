    <div id="main-view">
    <header class="header">
    <div class="header-inner">
        <a href="/" class="logo-link">
            <h1>Laissez-Faire T.C.</h1>
        </a>
        <button class="hamburger" id="hamburger">
            ☰
        </button>
    </div>

    <nav class="nav" id="nav">
        <ul>
            <li><a href="#about">About</a></li>
            <li class="has-submenu">
                <a href="#activity" class="submenu-main-link">Activity</a>
                <button class="submenu-toggle" id="activityToggle">
                    <span class="arrow">▼</span>
                </button>
                <ul class="submenu" id="activityMenu">
                    <li><a href="#practice">Practice</a></li>
                    <li><a href="#event">Event</a></li>
                    <li><a href="#schedule">Schedule</a></li>
                </ul>
            </li>
            <li><a href="#news">News</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="/portal">会員ページ</a></li>
        </ul>
    </nav>
  </header>

  <main>
    <!-- Topページ -->
    <section id="top" class="main-visual">
        <img src="/public/images/top.jpg" alt="サークル集合写真" class="top-image">
    </section>

    <!-- クイックニュース -->
    <div class="news-quick-nav">
        <div class="quick-nav-inner">
            <h4>Latest News</h4>
            <ul></ul>
        </div>
    </div>

    <!-- About -->
    <section id="about" class="section-container">
        <h2 class="section-title">About</h2>
        <div class="section-line"></div>

        <div class="about-main-flex">
            <!-- 左側 -->
            <div class="about-left-content">
                <div class="about-text">
                    <p>Laissez-Faire T.C.(レッセフェール、通称レッセ)は<br><strong>オール早稲田理工・飲酒禁止・初心者歓迎</strong>のテニスサークルです！</p>
                    <p>初心者から経験者まで同じコートで本気になれる雰囲気と、学年・性別関係なく仲が良いアットホームさが自慢です。</p>
                    <p>季節ごとのイベントや企画も充実していて<br>テニスも大学生活も全力で楽しみたい理工生にぴったりの環境が整っています。<br>活動場所は理工キャンパスの最寄駅から一本でアクセスできるため、通いやすさも魅力のひとつです！</p>
                    <p>楽しさも成長も欲張りたい方、ぜひレッセにお越しください！</p>
                </div>

                <h3 class="sub-title">Information</h3>
                <div class="about-data">
                    <table class="data-table">
                        <tr>
                            <th>人数</th>
                            <td>約200名（オール早稲田理工）</td>
                        </tr>
                        <tr>
                            <th>男女比</th>
                            <td>男 2 : 女 1</td>
                        </tr>
                        <tr>
                            <th>活動場所</th>
                            <td>城北中央公園（3～11月）<br>木場公園・光が丘公園（12～2月）</td>
                        </tr>
                        <tr>
                            <th>活動頻度</th>
                            <td>週3日（月曜・金曜 ＋ 火曜or水曜）</td>
                        </tr>
                        <tr>
                            <th>活動時間</th>
                            <td>19:00～21:00<br>(5限後に来る人もいます！)</td>
                        </tr>
                    </table>
                    <div class="achievements-box">
                        <h4 class="ach-title">🏆 Recent Achievements</h4>
                        <div class="ach-row">
                            <span class="ach-event">新早連（学内団体戦）</span>
                            <span class="ach-result">1部昇格</span>
                        </div>
                        <div class="ach-row">
                            <span class="ach-event">エスパジオ（団体戦）</span>
                            <span class="ach-result">ベスト4</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 右側 -->
            <div class="about-right-maps pc-only">
            <h3 class="sub-title">Court Access</h3>
                <div class="map-item">
                    <p>城北中央公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3128.1566679037232!2d139.6723661112183!3d35.756491272448685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018edaef27ce76f%3A0xa8014bc44342fff5!2z5Z-O5YyX5Lit5aSu5YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699784707!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-item">
                    <p>光が丘公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3127.817840835437!2d139.6310579!3d35.765108999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018ec6cb00dc049%3A0x6af88e8110b9ae26!2z5YWJ44GM5LiY5YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699625178!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-item">
                    <p>木場公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3131.2641668328565!2d139.8076515!3d35.677371!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018891b6d808137%3A0x1c41508ef88b5daf!2z5pyo5aC05YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699713538!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Activity -->
    <section id="activity" class="section-container">
        <h2 class="section-title">Activity</h2>
        <div class="section-line"></div>
        <p>日々の練習に加え合宿やイベントなども行っており、テニスだけでなくサークルとしての交流も大切にしています。<br>経験やレベルに関係なく、無理なく参加できる雰囲気なので、初心者から経験者まで楽しみながら活動できる環境です！</p>

        <div class="activity-block">
            <h3 id="practice">Practice</h3>
            <div class="photo-grid">
                <div class="photo-item">
                    <img src="/public/images/practice1.jpg" alt="練習風景1">
                    <p class="photo-caption">ナイターで練習中！</p>
                </div>
                <div class="photo-item">
                    <img src="/public/images/practice2.jpg" alt="練習風景2">
                    <p class="photo-caption">学年を超えて仲良くなれます！</p>
                </div>
                <div class="photo-item">
                    <img src="/public/images/practice3.jpg" alt="練習風景3">
                    <p class="photo-caption">練習後は近くのサイゼへ！</p>
                </div>
            </div>

            <h3 id="event">Event</h3>
            <div class="photo-grid">
                <div class="photo-item">
                    <img src="/public/images/event1.jpg" alt="イベント1">
                    <p class="photo-caption">ドレスコードでクリスマスパーティー!</p>
                </div>
                <div class="photo-item">
                    <img src="/public/images/event2.jpg" alt="イベント2">
                    <p class="photo-caption">春休みにはスキー合宿があります！</p>
                </div>
                <div class="photo-item">
                    <img src="/public/images/event3.jpg" alt="イベント3">
                    <p class="photo-caption">夏合宿は練習以外にも楽しい企画が盛りだくさん！</p>
                </div>
            </div>

            <h3 id="schedule">Schedule</h3>
            <div class="schedule-grid">
                <div class="schedule-card selected" data-schedule="april" onclick="openArticle('april')"><h4>4月</h4><p>新歓</p></div>
                <div class="schedule-card" data-schedule="may" onclick="openArticle('may')"><h4>5月</h4><p>ラケットツアー</p></div>
                <div class="schedule-card" data-schedule="june" onclick="openArticle('june')"><h4>6月</h4><p>総会</p></div>
                <div class="schedule-card" data-schedule="july" onclick="openArticle('july')"><h4>7月</h4><p>テスト</p></div>
                <div class="schedule-card" data-schedule="august" onclick="openArticle('august')"><h4>8月</h4><p>企画</p></div>
                <div class="schedule-card" data-schedule="september" onclick="openArticle('september')"><h4>9月</h4><p>夏合宿</p></div>
                <div class="schedule-card" data-schedule="october" onclick="openArticle('october')"><h4>10月</h4><p>ハロウィン</p></div>
                <div class="schedule-card" data-schedule="november" onclick="openArticle('november')"><h4>11月</h4><p>エスパジオ</p></div>
                <div class="schedule-card" data-schedule="december" onclick="openArticle('december')"><h4>12月</h4><p>クリパ</p></div>
                <div class="schedule-card" data-schedule="january" onclick="openArticle('january')"><h4>1月</h4><p>テスト</p></div>
                <div class="schedule-card" data-schedule="february" onclick="openArticle('february')"><h4>2月</h4><p>スキー合宿</p></div>
                <div class="schedule-card" data-schedule="march" onclick="openArticle('march')"><h4>3月</h4><p>春合宿</p></div>
            </div>

            <!-- モバイル用地図 -->
            <div class="about-right-maps mobile-only">
            <h3 class="sub-title"> Court Access </h3>
                <div class="map-item">
                    <p>城北中央公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3128.1566679037232!2d139.6723661112183!3d35.756491272448685!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018edaef27ce76f%3A0xa8014bc44342fff5!2z5Z-O5YyX5Lit5aSu5YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699784707!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-item">
                    <p>光が丘公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3127.817840835437!2d139.6310579!3d35.765108999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018ec6cb00dc049%3A0x6af88e8110b9ae26!2z5YWJ44GM5LiY5YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699625178!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="map-item">
                    <p>木場公園</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3131.2641668328565!2d139.8076515!3d35.677371!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018891b6d808137%3A0x1c41508ef88b5daf!2z5pyo5aC05YWs5ZySIOODhuODi-OCueOCs-ODvOODiA!5e1!3m2!1sja!2sjp!4v1770699713538!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- News -->
    <section id="news" class="section-container">
        <h2 class="section-title">News</h2>
        <div class="section-line"></div>

        <div class="news-grid"></div>
    </section>

    <div id="newsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNewsModal()">&times;</span>
            <div class="modal-body">
                <img id="modalImg" src="" alt="モーダル画像">
                <div class="modal-text">
                    <span id="modalDate" class="date"></span>
                    <h3 id="modalTitle"></h3>
                    <p id="modalDesc"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact -->
    <section id="contact" class="section-container">
        <h2 class="section-title">Contact</h2>
        <div class="section-line"></div>
        <p>入会希望や質問は、公式SNSのDMまでお気軽にどうぞ！</p>

        <div class="sns-links">
            <a href="https://www.instagram.com/laissezfairetc_2026?igsh=amthYmhyNXVkZzNn" target="_blank" class="sns-btn insta">Instagram</a>
            <a href="https://twitter.com/@laissez_2026" target="_blank" class="sns-btn x-twitter">X (Twitter)</a>
        </div>
    </section>
  </main>

  <footer>
    <p>© <?= date('Y') ?> 早稲田大学 Laissez-Faire T.C.</p>
  </footer>
  </div>

  <div id="detail-view" style="display:none;">
    <div class="slideshow-container">
        <button class="slide-ctrl prev" onclick="changeSlide(-1)">❮</button>
        <button class="slide-ctrl next" onclick="changeSlide(1)">❯</button>
        <div id="slide-wrapper"></div>
        <div class="slide-num" id="slide-counter">1 / 1</div>
    </div>

    <article class="article-content">
        <header class="article-header">
            <span class="article-month" id="article-month-tag"></span>
            <h2 class="article-title" id="article-title"></h2>
        </header>
        <div class="article-grid">
            <div class="main-text" id="article-text"></div>
            <aside class="side-content">
                <div class="special-card">
                    <h4>🙋 新入生 Q&A</h4>
                    <div id="extra-content"></div>
                </div>
            </aside>
        </div>
    </article>
    <div class="detail-nav">
        <div class="back-btn" onclick="closeArticle()">← BACK TO HOME</div>
    </div>
  </div>
