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

                                <div class="match-scores" style="display: flex; justify-content: center; gap: 15px; margin: 8px 0; padding: 4px; background: #f0ead8; border-radius: 20px;">
                                    <span style="font-size: 16px; font-weight: bold; color: <?= (($match['player1_score'] ?? 0) > 0) ? '#2c5f2d' : '#999' ?>"><?= $match['player1_score'] ?? 0 ?></span>
                                    <span style="color: #c9a84c; font-weight: bold;">:</span>
                                    <span style="font-size: 16px; font-weight: bold; color: <?= (($match['player2_score'] ?? 0) > 0) ? '#2c5f2d' : '#999' ?>"><?= $match['player2_score'] ?? 0 ?></span>
                                </div>

                                <div class="match-players">
                                    <div class="match-player <?= (isset($match['winner_id']) && $match['winner_id'] == $match['player1_id']) ? 'winner' : '' ?>" data-player-id="<?= $match['player1_id'] ?>">
                                        <div class="player-avatar">♜</div>
                                        <div class="player-info">
                                            <div class="player-name"><?= htmlspecialchars($match['p1_name'] ?? '— ОЖИДАНИЕ —') ?></div>
                                            <?php if (empty($match['winner_id']) && !empty($match['player1_id']) && $isAdmin): ?>
                                                <button class="win-button" data-match-id="<?= $match['id'] ?>" data-winner-id="<?= $match['player1_id'] ?>">ПОБЕДА</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="match-vs">⚔️</div>

                                    <div class="match-player <?= (isset($match['winner_id']) && $match['winner_id'] == $match['player2_id']) ? 'winner' : '' ?>" data-player-id="<?= $match['player2_id'] ?>">
                                        <div class="player-avatar">♞</div>
                                        <div class="player-info">
                                            <div class="player-name"><?= htmlspecialchars($match['p2_name'] ?? '— ОЖИДАНИЕ —') ?></div>
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

        function handleWin(button) {
            const matchId = button.dataset.matchId;
            const winnerId = button.dataset.winnerId;
            const matchCard = button.closest('.match-card');

            button.disabled = true;
            button.textContent = '...';

            fetch('index.php?action=set_winner_ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'match_id=' + matchId + '&winner_id=' + winnerId
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateMatchUI(matchCard, winnerId, data);
                        if (data.new_round) addNewRound(data.new_round);
                        if (data.champion) showChampion(data.champion);
                        showNotification('ПОБЕДА ЗАСЧИТАНА!', 'success');
                    } else {
                        showNotification(data.error || 'ОШИБКА!', 'error');
                        button.disabled = false;
                        button.textContent = 'ПОБЕДА';
                    }
                })
                .catch(() => {
                    showNotification('ОШИБКА СОЕДИНЕНИЯ!', 'error');
                    button.disabled = false;
                    button.textContent = 'ПОБЕДА';
                });
        }

        function updateMatchUI(matchCard, winnerId, data) {
            const scoresDiv = matchCard.querySelector('.match-scores');
            if (scoresDiv && data.new_scores) {
                scoresDiv.innerHTML = `
                <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player1 > 0 ? '#2c5f2d' : '#999'}">${data.new_scores.player1}</span>
                <span style="color: #c9a84c; font-weight: bold;">:</span>
                <span style="font-size: 16px; font-weight: bold; color: ${data.new_scores.player2 > 0 ? '#2c5f2d' : '#999'}">${data.new_scores.player2}</span>
            `;
            }

            if (data.match_completed) {
                matchCard.querySelectorAll('.match-player').forEach(p => {
                    if (p.dataset.playerId == winnerId) p.classList.add('winner');
                });
                matchCard.querySelectorAll('.win-button').forEach(btn => btn.remove());
                const completedSpan = matchCard.querySelector('.match-completed');
                if (completedSpan) completedSpan.style.display = 'inline-block';
            } else {
                const btn = matchCard.querySelector('.win-button');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = 'ПОБЕДА';
                }
            }
        }

        function addNewRound(roundData) {
            if (document.querySelector(`.round[data-round-id="${roundData.round_id}"]`)) return;

            const roundsGrid = document.querySelector('.rounds-grid');
            const roundDiv = document.createElement('div');
            roundDiv.className = 'round';
            roundDiv.setAttribute('data-round-id', roundData.round_id);
            roundDiv.innerHTML = `
            <div class="round-title">${roundData.round_name.replace('Round', 'РАУНД')}<span class="round-number">#${roundData.round_number}</span></div>
            <div class="matches-container">
                ${roundData.matches.map(m => `
                    <div class="match-card" data-match-id="${m.id}">
                        <div class="match-header">
                            <span class="match-number">МАТЧ ${m.id}</span>
                            <span class="match-completed" style="display: none">✓ ЗАВЕРШЕН</span>
                        </div>
                        <div class="match-scores" style="display: flex; justify-content: center; gap: 15px; margin: 8px 0; padding: 4px; background: #f0ead8; border-radius: 20px;">
                            <span style="font-size: 16px; font-weight: bold; color: #999">0</span>
                            <span style="color: #c9a84c; font-weight: bold;">:</span>
                            <span style="font-size: 16px; font-weight: bold; color: #999">0</span>
                        </div>
                        <div class="match-players">
                            <div class="match-player" data-player-id="${m.player1_id || ''}">
                                <div class="player-avatar">♜</div>
                                <div class="player-info">
                                    <div class="player-name">${m.p1_name || '— ОЖИДАНИЕ —'}</div>
                                    ${m.player1_id && m.can_edit ? `<button class="win-button" data-match-id="${m.id}" data-winner-id="${m.player1_id}">ПОБЕДА</button>` : ''}
                                </div>
                            </div>
                            <div class="match-vs">⚔️</div>
                            <div class="match-player" data-player-id="${m.player2_id || ''}">
                                <div class="player-avatar">♞</div>
                                <div class="player-info">
                                    <div class="player-name">${m.p2_name || '— ОЖИДАНИЕ —'}</div>
                                    ${m.player2_id && m.can_edit ? `<button class="win-button" data-match-id="${m.id}" data-winner-id="${m.player2_id}">ПОБЕДА</button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
            roundsGrid.appendChild(roundDiv);
            attachWinHandlers();
            roundDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
        }

        function showChampion(name) {
            let champBlock = document.querySelector('.champion-card');
            if (champBlock) {
                champBlock.querySelector('.champion-name').textContent = name;
                champBlock.style.display = 'block';
            } else {
                const wrapper = document.querySelector('.bracket-wrapper');
                const newChamp = document.createElement('div');
                newChamp.className = 'champion-card';
                newChamp.innerHTML = `<h2>ЧЕМПИОН ТУРНИРА</h2><div class="champion-name">${name}</div><div style="font-size: 28px;">♔ ♕ ♚</div>`;
                wrapper.parentNode.insertBefore(newChamp, wrapper);
            }
            const resetBtn = document.querySelector('.btn-reset');
            if (resetBtn) resetBtn.style.display = 'none';
        }

        function showNotification(msg, type) {
            const notif = document.createElement('div');
            notif.textContent = msg;
            notif.style.cssText = `position:fixed; bottom:20px; right:20px; padding:12px 20px; background:${type === 'success' ? '#2c5f2d' : '#8b5a4c'}; color:#e8d5a8; border-radius:8px; z-index:2000; font-family:Georgia,serif; animation:slideIn 0.3s ease;`;
            document.body.appendChild(notif);
            setTimeout(() => {
                notif.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notif.remove(), 300);
            }, 2000);
        }

        function attachWinHandlers() {
            document.querySelectorAll('.win-button').forEach(btn => {
                btn.removeEventListener('click', btn._handler);
                const handler = (e) => {
                    e.preventDefault();
                    handleWin(btn);
                };
                btn.addEventListener('click', handler);
                btn._handler = handler;
            });
        }

        const style = document.createElement('style');
        style.textContent = `@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}@keyframes slideOut{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}`;
        document.head.appendChild(style);

        attachWinHandlers();
    });
</script>