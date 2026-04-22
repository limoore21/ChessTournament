<h2 class="section-title">ТУРНИРНАЯ СЕТКА</h2>

<?php if (isset($final_winner) && $final_winner): ?>
    <div class="champion-card">
        <h2>ЧЕМПИОН ТУРНИРА</h2>
        <div class="champion-name"><?= htmlspecialchars($final_winner) ?></div>
        <div style="font-size: 28px;">♔ ♕ ♚</div>
    </div>
<?php endif; ?>

<?php if (empty($rounds)): ?>
    <div class="info-box">
        Сетка не сформирована. Проведите жеребьевку.
        <?php if ($isAdmin): ?>
            <br><br><a href="index.php?action=draw" class="btn-start">ПРОВЕСТИ ЖЕРЕБЬЕВКУ</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="bracket-wrapper">
        <div class="rounds-grid">
            <?php foreach ($rounds as $round_id => $matches): ?>
                <div class="round">
                    <div class="round-title">
                        <?= str_replace('Round', 'РАУНД', htmlspecialchars($matches[0]['round_name'] ?? 'РАУНД')) ?>
                        <span class="round-number">#<?= $round_id ?></span>
                    </div>
                    <div class="matches-container">
                        <?php foreach ($matches as $match): ?>
                            <div class="match-card" data-match-id="<?= $match['id'] ?>">
                                <div class="match-header">
                                    <span class="match-number">МАТЧ <?= $match['id'] ?></span>
                                    <span class="match-completed" style="display: <?= !empty($match['winner_id']) ? 'inline-block' : 'none' ?>">✓ ЗАВЕРШЕН</span>
                                </div>

                                <!-- Счет матча -->
                                <div class="match-scores" style="display: flex; justify-content: center; gap: 15px; margin: 8px 0; padding: 4px; background: #f0ead8; border-radius: 20px;">
                                    <span style="font-size: 16px; font-weight: bold; color: <?= (($match['player1_score'] ?? 0) > 0) ? '#2c5f2d' : '#999' ?>">
                                        <?= $match['player1_score'] ?? 0 ?>
                                    </span>
                                    <span style="color: #c9a84c; font-weight: bold;">:</span>
                                    <span style="font-size: 16px; font-weight: bold; color: <?= (($match['player2_score'] ?? 0) > 0) ? '#2c5f2d' : '#999' ?>">
                                        <?= $match['player2_score'] ?? 0 ?>
                                    </span>
                                </div>

                                <div class="match-players">
                                    <div class="match-player <?= (isset($match['winner_id']) && $match['winner_id'] == $match['player1_id']) ? 'winner' : '' ?>" data-player-id="<?= $match['player1_id'] ?>">
                                        <div class="player-avatar">♜</div>
                                        <div class="player-info">
                                            <div class="player-name">
                                                <?= htmlspecialchars($match['p1_name'] ?? '— ОЖИДАНИЕ —') ?>
                                            </div>
                                            <?php if (empty($match['winner_id']) && !empty($match['player1_id']) && $isAdmin): ?>
                                                <button class="win-button" data-match-id="<?= $match['id'] ?>" data-winner-id="<?= $match['player1_id'] ?>">ПОБЕДА</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="match-vs">⚔️</div>

                                    <div class="match-player <?= (isset($match['winner_id']) && $match['winner_id'] == $match['player2_id']) ? 'winner' : '' ?>" data-player-id="<?= $match['player2_id'] ?>">
                                        <div class="player-avatar">♞</div>
                                        <div class="player-info">
                                            <div class="player-name">
                                                <?= htmlspecialchars($match['p2_name'] ?? '— ОЖИДАНИЕ —') ?>
                                            </div>
                                            <?php if (empty($match['winner_id']) && !empty($match['player2_id']) && $isAdmin): ?>
                                                <button class="win-button" data-match-id="<?= $match['id'] ?>" data-winner-id="<?= $match['player2_id'] ?>">ПОБЕДА</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($isAdmin && empty($final_winner) && !empty($rounds)): ?>
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php?action=draw" class="btn-reset" onclick="return confirm('Сбросить турнир? Все результаты будут потеряны!')">⟳ СБРОСИТЬ ТУРНИР</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Находим все кнопки победы
        const winButtons = document.querySelectorAll('.win-button');

        winButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const matchId = this.getAttribute('data-match-id');
                const winnerId = this.getAttribute('data-winner-id');
                const buttonElement = this;
                const matchCard = buttonElement.closest('.match-card');

                // Отключаем кнопку на время запроса
                buttonElement.disabled = true;
                buttonElement.textContent = '...';

                // Отправляем AJAX запрос
                fetch('index.php?action=set_winner_ajax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'match_id=' + encodeURIComponent(matchId) + '&winner_id=' + encodeURIComponent(winnerId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Обновляем счет
                            const scoresDiv = matchCard.querySelector('.match-scores');
                            if (scoresDiv && data.new_scores) {
                                scoresDiv.innerHTML = `
                            <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player1 > 0 ? '#2c5f2d' : '#999'}">
                                ${data.new_scores.player1}
                            </span>
                            <span style="color: #c9a84c; font-weight: bold;">:</span>
                            <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player2 > 0 ? '#2c5f2d' : '#999'}">
                                ${data.new_scores.player2}
                            </span>
                        `;
                            }

                            // Если матч завершен
                            if (data.match_completed) {
                                // Подсвечиваем победителя
                                const players = matchCard.querySelectorAll('.match-player');
                                players.forEach(player => {
                                    const playerId = player.getAttribute('data-player-id');
                                    if (playerId == winnerId) {
                                        player.classList.add('winner');
                                    }
                                });

                                // Убираем все кнопки победы в этом матче
                                const buttons = matchCard.querySelectorAll('.win-button');
                                buttons.forEach(btn => btn.remove());

                                // Отмечаем матч как завершенный
                                const completedSpan = matchCard.querySelector('.match-completed');
                                if (completedSpan) {
                                    completedSpan.style.display = 'inline-block';
                                }
                            } else {
                                // Включаем кнопку обратно для следующей партии
                                buttonElement.disabled = false;
                                buttonElement.textContent = 'ПОБЕДА';
                            }

                            // Если есть новый раунд - добавляем его
                            if (data.new_round) {
                                addNewRound(data.new_round);
                            }

                            // Если есть чемпион - показываем
                            if (data.champion) {
                                showChampion(data.champion);
                            }

                            // Показываем уведомление
                            showNotification('ПОБЕДА ЗАСЧИТАНА!', 'success');
                        } else {
                            showNotification(data.error || 'ОШИБКА!', 'error');
                            buttonElement.disabled = false;
                            buttonElement.textContent = 'ПОБЕДА';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('ОШИБКА СОЕДИНЕНИЯ!', 'error');
                        buttonElement.disabled = false;
                        buttonElement.textContent = 'ПОБЕДА';
                    });
            });
        });

        function addNewRound(roundData) {
            // Проверяем, есть ли уже такой раунд
            const existingRound = document.querySelector(`.round[data-round-id="${roundData.round_id}"]`);
            if (existingRound) return;

            // Создаем новый раунд
            const roundsGrid = document.querySelector('.rounds-grid');
            const newRound = document.createElement('div');
            newRound.className = 'round';
            newRound.setAttribute('data-round-id', roundData.round_id);

            newRound.innerHTML = `
            <div class="round-title">
                ${roundData.round_name.replace('Round', 'РАУНД')}
                <span class="round-number">#${roundData.round_number}</span>
            </div>
            <div class="matches-container">
                ${roundData.matches.map(match => `
                    <div class="match-card" data-match-id="${match.id}">
                        <div class="match-header">
                            <span class="match-number">МАТЧ ${match.id}</span>
                            <span class="match-completed" style="display: none">✓ ЗАВЕРШЕН</span>
                        </div>
                        <div class="match-scores" style="display: flex; justify-content: center; gap: 15px; margin: 8px 0; padding: 4px; background: #f0ead8; border-radius: 20px;">
                            <span style="font-size: 16px; font-weight: bold; color: #999">0</span>
                            <span style="color: #c9a84c; font-weight: bold;">:</span>
                            <span style="font-size: 16px; font-weight: bold; color: #999">0</span>
                        </div>
                        <div class="match-players">
                            <div class="match-player" data-player-id="${match.player1_id || ''}">
                                <div class="player-avatar">♜</div>
                                <div class="player-info">
                                    <div class="player-name">${match.p1_name || '— ОЖИДАНИЕ —'}</div>
                                    ${match.player1_id && match.can_edit ? `<button class="win-button" data-match-id="${match.id}" data-winner-id="${match.player1_id}">ПОБЕДА</button>` : ''}
                                </div>
                            </div>
                            <div class="match-vs">⚔️</div>
                            <div class="match-player" data-player-id="${match.player2_id || ''}">
                                <div class="player-avatar">♞</div>
                                <div class="player-info">
                                    <div class="player-name">${match.p2_name || '— ОЖИДАНИЕ —'}</div>
                                    ${match.player2_id && match.can_edit ? `<button class="win-button" data-match-id="${match.id}" data-winner-id="${match.player2_id}">ПОБЕДА</button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

            roundsGrid.appendChild(newRound);

            // Добавляем обработчики для новых кнопок
            attachWinButtonHandlers();

            // Плавно прокручиваем к новому раунду
            newRound.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
        }

        function attachWinButtonHandlers() {
            const newButtons = document.querySelectorAll('.win-button');
            newButtons.forEach(button => {
                // Убираем старые обработчики, чтобы не дублировать
                const oldHandler = button._handler;
                if (oldHandler) button.removeEventListener('click', oldHandler);

                const handler = function(e) {
                    e.preventDefault();
                    const matchId = this.getAttribute('data-match-id');
                    const winnerId = this.getAttribute('data-winner-id');
                    const buttonElement = this;
                    const matchCard = buttonElement.closest('.match-card');

                    buttonElement.disabled = true;
                    buttonElement.textContent = '...';

                    fetch('index.php?action=set_winner_ajax', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'match_id=' + encodeURIComponent(matchId) + '&winner_id=' + encodeURIComponent(winnerId)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Обновляем счет
                                const scoresDiv = matchCard.querySelector('.match-scores');
                                if (scoresDiv && data.new_scores) {
                                    scoresDiv.innerHTML = `
                                <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player1 > 0 ? '#2c5f2d' : '#999'}">
                                    ${data.new_scores.player1}
                                </span>
                                <span style="color: #c9a84c; font-weight: bold;">:</span>
                                <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player2 > 0 ? '#2c5f2d' : '#999'}">
                                    ${data.new_scores.player2}
                                </span>
                            `;
                                }

                                if (data.match_completed) {
                                    const players = matchCard.querySelectorAll('.match-player');
                                    players.forEach(player => {
                                        const playerId = player.getAttribute('data-player-id');
                                        if (playerId == winnerId) {
                                            player.classList.add('winner');
                                        }
                                    });
                                    const buttons = matchCard.querySelectorAll('.win-button');
                                    buttons.forEach(btn => btn.remove());
                                    const completedSpan = matchCard.querySelector('.match-completed');
                                    if (completedSpan) completedSpan.style.display = 'inline-block';
                                } else {
                                    buttonElement.disabled = false;
                                    buttonElement.textContent = 'ПОБЕДА';
                                }

                                if (data.new_round) addNewRound(data.new_round);
                                if (data.champion) showChampion(data.champion);
                                showNotification('ПОБЕДА ЗАСЧИТАНА!', 'success');
                            } else {
                                showNotification(data.error || 'ОШИБКА!', 'error');
                                buttonElement.disabled = false;
                                buttonElement.textContent = 'ПОБЕДА';
                            }
                        })
                        .catch(error => {
                            showNotification('ОШИБКА СОЕДИНЕНИЯ!', 'error');
                            buttonElement.disabled = false;
                            buttonElement.textContent = 'ПОБЕДА';
                        });
                };
                button.addEventListener('click', handler);
                button._handler = handler;
            });
        }

        function showChampion(championName) {
            let championBlock = document.querySelector('.champion-card');

            if (championBlock) {
                championBlock.querySelector('.champion-name').textContent = championName;
                championBlock.style.display = 'block';
            } else {
                const bracketWrapper = document.querySelector('.bracket-wrapper');
                const newChampion = document.createElement('div');
                newChampion.className = 'champion-card';
                newChampion.innerHTML = `
                <h2>ЧЕМПИОН ТУРНИРА</h2>
                <div class="champion-name">${championName}</div>
                <div style="font-size: 28px;">♔ ♕ ♚</div>
            `;
                bracketWrapper.parentNode.insertBefore(newChampion, bracketWrapper);
            }

            const resetBtn = document.querySelector('.btn-reset');
            if (resetBtn) resetBtn.style.display = 'none';
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background: ${type === 'success' ? '#2c5f2d' : '#8b5a4c'};
            color: #e8d5a8;
            border-radius: 8px;
            font-size: 14px;
            z-index: 2000;
            animation: slideIn 0.3s ease;
            font-family: Georgia, serif;
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 2000);
        }

        // Добавляем анимации в стили
        const style = document.createElement('style');
        style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
        document.head.appendChild(style);
    });
</script>