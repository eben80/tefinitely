(async function() {
    if (document.getElementById("folscan-popup")) return;

    const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    const popup = document.createElement("div");
    popup.id = "folscan-popup-container";
    popup.innerHTML = `
    <style>
        #folscan-launcher { position: fixed; top: 20px; right: 120px; background: gold; color: black; font-weight: bold; padding: 10px 15px; border-radius: 50px; cursor: pointer; z-index: 100001; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-family: sans-serif; font-size: 14px; }
        #folscan-popup { position: fixed; top: 5%; left: 5%; width: 90%; max-height: 90%; overflow-y: auto; background: #1e1e1e; color: white; font-family: sans-serif; padding: 20px; border: 2px solid #666; border-radius: 10px; z-index: 100000; box-shadow: 0 0 20px #000; display: none; }
        #folscan-popup h2 { text-align: center; font-size: 24px; margin-bottom: 16px; }
        #folscan-buttons { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; align-items: center; }
        #folscan-buttons select, #folscan-buttons input, #folscan-buttons button { background: #222; color: white; border: 1px solid #555; border-radius: 5px; padding: 6px 10px; font-size: 14px; }
        #folscan-buttons button { background: #444; cursor: pointer; }
        #fetch-status { margin-top: 10px; font-size: 16px; color: lightgreen; }
        .pulse { animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
        .folscan-section { margin: 10px 0; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .folscan-section summary { font-weight: bold; cursor: pointer; padding: 5px; }
        .folscan-link { color: inherit; text-decoration: none; flex: 1; margin: 2px 0; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .folscan-link:hover { text-decoration: underline; }
        .folscan-row { display: flex; align-items: center; gap: 8px; padding: 4px 0; border-bottom: 1px solid #2a2a2a; }
        .folscan-row:last-child { border-bottom: none; }
        .folscan-meta { display: flex; gap: 6px; width: 100px; justify-content: flex-end; }
        .folscan-meta span { cursor: help; font-size: 12px; filter: grayscale(1); opacity: 0.3; }
        .folscan-meta span.active { filter: grayscale(0); opacity: 1; }
        .folscan-header-row { display: flex; align-items: center; gap: 8px; padding-bottom: 5px; margin-bottom: 5px; border-bottom: 1px solid #444; font-size: 11px; color: #888; font-weight: bold; }
        #premium-badge { color: gold; font-weight: bold; margin-left: 10px; display: none; }
        #launcher-premium-crown { display: none; margin-left: 5px; }
    </style>
    <div id="folscan-launcher">FolScan<span id="launcher-premium-crown">👑</span></div>
    <div id="folscan-popup">
        <h2>FolScan <span id="premium-badge">👑 PREMIUM</span></h2>
        <div id="folscan-buttons">
            <select id="folscan-userlist"><option value="">Select Previous...</option></select>
            <input type="text" id="folscan-username" placeholder="Enter username" />
            <button id="folscan-run">Run Report</button>
            <button id="folscan-download" disabled>Download JSON</button>
            <button id="folscan-reset">Reset All</button>
            <button id="folscan-close">Close</button>
        </div>
        <div id="fetch-status"></div>
        <div id="folscan-report"></div>
    </div>`;
    document.body.appendChild(popup);

    const launcher = document.getElementById("folscan-launcher"),
          launcherCrown = document.getElementById("launcher-premium-crown"),
          popupDiv = document.getElementById("folscan-popup"),
          userInp = document.getElementById("folscan-username"),
          userList = document.getElementById("folscan-userlist"),
          runBtn = document.getElementById("folscan-run"),
          dlBtn = document.getElementById("folscan-download"),
          resetBtn = document.getElementById("folscan-reset"),
          closeBtn = document.getElementById("folscan-close"),
          status = document.getElementById("fetch-status"),
          report = document.getElementById("folscan-report"),
          premiumBadge = document.getElementById("premium-badge");

    let currentTarget = "";
    const storageKey = "folscan_usernames";

    const getAccountTier = async () => {
        const data = await chrome.storage.local.get(["isPremium", "isPro"]);
        return { isPremium: !!data.isPremium, isPro: !!data.isPro };
    };

    const updatePremiumUI = async () => {
        chrome.storage.local.get(["isPremium", "isPro"], (data) => {
            if (data.isPremium) {
                premiumBadge.style.display = "inline";
                premiumBadge.textContent = data.isPro ? "👑 PREMIUM PRO" : "👑 PREMIUM";
                launcherCrown.style.display = "inline";
                launcherCrown.style.color = data.isPro ? "cyan" : "white";
            } else {
                premiumBadge.style.display = "none";
                launcherCrown.style.display = "none";
            }
        });
    };

    const updateDropdown = () => {
        chrome.storage.local.get(storageKey, (data) => {
            const list = data[storageKey] || [];
            userList.innerHTML = '<option value="">Select Previous...</option>';
            list.forEach(u => {
                const opt = document.createElement("option");
                opt.value = u;
                opt.textContent = u;
                userList.appendChild(opt);
            });
        });
    };

    userList.addEventListener("change", () => {
        if (userList.value) {
            userInp.value = userList.value;
            currentTarget = userList.value;
            displayPersistedReport(userList.value);
        }
    });

    userInp.addEventListener("input", () => {
        const u = userInp.value.trim();
        if (u) {
            currentTarget = u;
            displayPersistedReport(u);
        } else {
            report.innerHTML = "";
            status.innerText = "";
        }
    });

    const saveUserToHistory = u => {
        chrome.storage.local.get(storageKey, (data) => {
            let list = data[storageKey] || [];
            if (!list.includes(u)) {
                list.unshift(u);
                chrome.storage.local.set({ [storageKey]: list }, updateDropdown);
            }
        });
    };

    const safeFetch = url => fetch(url).then(res => {
        if (res.status === 429) throw new Error("Rate Limited. Please wait 1 hour.");
        return res.json();
    });

    const isLoggedIn = () => {
        // Simple check for Instagram's logged-in state (search for common elements or cookie indicators)
        return document.cookie.includes("ds_user_id") || !!document.querySelector("nav") || !!document.querySelector('a[href*="/direct/inbox/"]');
    };

    const getCurrentViewerUsername = () => {
        const config = Array.from(document.querySelectorAll('script')).find(s => s.textContent.includes('viewer'))?.textContent;
        if (config) {
            try {
                const match = config.match(/"username":"([^"]+)"/);
                if (match) return match[1];
            } catch (e) {}
        }
        // Fallback: try to find it in common UI elements
        const profileLink = document.querySelector('a[href^="/"][href$="/"] img[alt*="profile"]')?.closest('a')?.href;
        if (profileLink) return profileLink.split('/').filter(Boolean).pop();

        return null;
    };

    const renderReportUI = (username, sections, isPremium, timestamp = null) => {
        report.innerHTML = "";
        const title = document.createElement("h3");
        title.textContent = `📊 Report for @${username}`;
        report.appendChild(title);

        if (timestamp) {
            const tsDiv = document.createElement("div");
            tsDiv.style.fontSize = "12px";
            tsDiv.style.color = "#aaa";
            tsDiv.style.marginBottom = "10px";
            tsDiv.textContent = `Last Scan: ${new Date(timestamp).toLocaleString()}`;
            report.appendChild(tsDiv);
        }

        if (!isPremium) {
            const limitMsg = document.createElement("p");
            limitMsg.style.color = "gold";
            limitMsg.textContent = "Limited to 100 items and basic reports for free users.";
            report.appendChild(limitMsg);
        }

        sections.forEach(s => {
            const isLocked = s.premium && !isPremium;
            const details = document.createElement("details");
            details.className = "folscan-section";
            if (s.list.length > 0 && !isLocked) details.open = true;

            const summary = document.createElement("summary");
            summary.style.color = s.color;
            summary.textContent = `${s.title} (${isLocked ? 'Premium Only' : s.list.length})`;
            details.appendChild(summary);

            const div = document.createElement("div");
            div.style.paddingLeft = "15px";

            if (isLocked) {
                const em = document.createElement("em");
                em.textContent = "Upgrade to Premium to see who unfollowed you!";
                div.appendChild(em);
            } else if (s.list.length === 0) {
                const em = document.createElement("em");
                em.textContent = "None";
                div.appendChild(em);
            } else {
                const headerRow = document.createElement("div");
                headerRow.className = "folscan-header-row";
                headerRow.innerHTML = `
                    <div style="flex: 1">User</div>
                    <div class="folscan-meta">
                        <span title="Private Account">🔒</span>
                        <span title="Verified Account">✅</span>
                        <span title="Requested by You">📤</span>
                        <span title="Requested You">📥</span>
                    </div>`;
                div.appendChild(headerRow);

                s.list.forEach(u => {
                    const row = document.createElement("div");
                    row.className = "folscan-row";

                    const a = document.createElement("a");
                    a.className = "folscan-link";
                    a.href = `https://instagram.com/${u.username}`;
                    a.target = "_blank";
                    a.textContent = `@${u.username} (${u.full_name || 'No Name'})`;

                    const meta = document.createElement("div");
                    meta.className = "folscan-meta";

                    const createIcon = (active, title, icon) => {
                        const s = document.createElement("span");
                        s.textContent = icon;
                        s.title = title;
                        if (active) s.className = "active";
                        return s;
                    };

                    meta.appendChild(createIcon(u.is_private, "Private Account", "🔒"));
                    meta.appendChild(createIcon(u.is_verified, "Verified Account", "✅"));
                    meta.appendChild(createIcon(u.requested_by_viewer, "Requested by You", "📤"));
                    meta.appendChild(createIcon(u.requested_viewer || u.has_requested_viewer, "Requested You", "📥"));

                    row.appendChild(a);
                    row.appendChild(meta);
                    div.appendChild(row);
                });
            }
            details.appendChild(div);
            report.appendChild(details);
        });
    };

    const displayPersistedReport = async (username) => {
        const { isPremium } = await getAccountTier();
        chrome.storage.local.get([`folscan_${username}_report`, `folscan_${username}_followers`, `folscan_${username}_followings`], (data) => {
            const savedReport = data[`folscan_${username}_report`];
            if (savedReport) {
                renderReportUI(username, savedReport.sections, isPremium, savedReport.timestamp);
                status.innerText = "Showing saved report. Click 'Run Report' for a fresh scan.";
                dlBtn.disabled = false;
            } else {
                report.innerHTML = "";
                status.innerText = "No previous scan found for this user.";
                dlBtn.disabled = true;
            }
        });
    };

    const runScan = async (username) => {
        if (!isLoggedIn()) {
            throw new Error("You must be logged in to Instagram to run a scan.");
        }
        const { isPremium, isPro } = await getAccountTier();
        const viewer = getCurrentViewerUsername();

        if (username.toLowerCase() !== viewer?.toLowerCase()) {
            if (!isPro) {
                const msg = isPremium ?
                    "Premium License only allows scanning your own account. Upgrade to Premium Pro to scan any public or followed account!" :
                    "Scanning other accounts requires a Premium Pro license. Note: Private accounts you don't follow cannot be scanned.";
                throw new Error(msg);
            }
        }

        runBtn.disabled = true; dlBtn.disabled = true; status.className = "pulse";
        status.innerText = `Connecting...`;

        const searchRes = await safeFetch(`https://www.instagram.com/web/search/topsearch/?query=${username}`);
        const userId = searchRes.users.find(u => u.user.username === username)?.user.pk;
        if (!userId) throw new Error("User not found.");

        const fetchAll = async (type, hash) => {
            let results = [], cursor = null, hasNext = true, pageCount = 0;
            const maxFree = 100;
            while (hasNext) {
                if (!isPremium && results.length >= maxFree) {
                    status.innerText = `Free limit reached (${maxFree}). Upgrade for more!`;
                    break;
                }
                status.innerText = `Fetching ${type}: ${results.length}...`;
                const url = `https://www.instagram.com/graphql/query/?query_hash=${hash}&variables=` +
                            encodeURIComponent(JSON.stringify({ id: userId, first: 50, after: cursor }));
                const data = await safeFetch(url);
                const page = data.data.user[type];
                results = results.concat(page.edges.map(({ node: n }) => ({
                    id: n.id,
                    username: n.username,
                    full_name: n.full_name,
                    is_private: n.is_private,
                    is_verified: n.is_verified,
                    requested_by_viewer: n.requested_by_viewer,
                    has_requested_viewer: n.has_requested_viewer
                })));
                hasNext = page.page_info.has_next_page;
                cursor = page.page_info.end_cursor;
                if (hasNext) {
                    pageCount++;
                    const delay = 3500 + Math.random() * 3000;
                    status.innerText = `Waiting ${Math.round(delay/1000)}s...`;
                    await sleep(delay);
                    if (pageCount % 10 === 0) await sleep(30000);
                }
            }
            return results;
        };

        const currentFollowers = await fetchAll("edge_followed_by", "c76146de99bb02f6415203be841dd25a");
        status.innerText = `Switching lists...`; await sleep(4000);
        const currentFollowings = await fetchAll("edge_follow", "d04b0a864b4b54837c0d870b0e77e076");

        chrome.storage.local.get([`folscan_${username}_followers`, `folscan_${username}_followings`], (data) => {
            // Support both old array format and new map format for migration
            const lastFollowersRaw = data[`folscan_${username}_followers`] || {};
            const lastFollowingsRaw = data[`folscan_${username}_followings`] || {};

            const toMap = (raw) => {
                if (Array.isArray(raw)) {
                    const map = {};
                    raw.forEach(u => { if (u.id) map[u.id] = { username: u.username, full_name: u.full_name }; });
                    return map;
                }
                return raw;
            };

            const lastFollowers = toMap(lastFollowersRaw);
            const lastFollowings = toMap(lastFollowingsRaw);

            const currentFollowersMap = {};
            currentFollowers.forEach(f => currentFollowersMap[f.id] = {
                username: f.username,
                full_name: f.full_name,
                is_private: f.is_private,
                is_verified: f.is_verified,
                requested_by_viewer: f.requested_by_viewer,
                has_requested_viewer: f.has_requested_viewer
            });
            const currentFollowingsMap = {};
            currentFollowings.forEach(f => currentFollowingsMap[f.id] = {
                username: f.username,
                full_name: f.full_name,
                is_private: f.is_private,
                is_verified: f.is_verified,
                requested_by_viewer: f.requested_by_viewer,
                has_requested_viewer: f.has_requested_viewer
            });

            const changedUsernames = [];
            for (const id in currentFollowersMap) {
                if (lastFollowers[id] && lastFollowers[id].username !== currentFollowersMap[id].username) {
                    changedUsernames.push({ id, old: lastFollowers[id].username, new: currentFollowersMap[id].username });
                }
            }
            for (const id in currentFollowingsMap) {
                if (lastFollowings[id] && lastFollowings[id].username !== currentFollowingsMap[id].username && !changedUsernames.some(c => c.id === id)) {
                    changedUsernames.push({ id, old: lastFollowings[id].username, new: currentFollowingsMap[id].username });
                }
            }

            const sections = [
                { title: "🆕 New Followers", list: currentFollowers.filter(f => !lastFollowers[f.id]), color: "lightgreen", premium: false },
                { title: "❌ Lost Followers", list: Object.keys(lastFollowers).filter(id => !currentFollowersMap[id]).map(id => ({ id, ...lastFollowers[id] })), color: "salmon", premium: true },
                { title: "🆕 New Followings", list: currentFollowings.filter(f => !lastFollowings[f.id]), color: "lightblue", premium: false },
                { title: "📤 Unfollowed (By You)", list: Object.keys(lastFollowings).filter(id => !currentFollowingsMap[id]).map(id => ({ id, ...lastFollowings[id] })), color: "#ff6b6b", premium: true },
                { title: "🚫 Not Following Back", list: currentFollowings.filter(f => !currentFollowersMap[f.id]), color: "orange", premium: false },
                { title: "🤝 Mutual", list: currentFollowings.filter(f => currentFollowersMap[f.id]), color: "#00d4ff", premium: false },
                { title: "📛 Username Changes", list: changedUsernames.map(c => ({ username: c.new, full_name: `was @${c.old}` })), color: "yellow", premium: true }
            ];

            // Sort: Items with lists > 0 come first
            sections.sort((a, b) => (b.list.length > 0) - (a.list.length > 0));

            const timestamp = Date.now();
            const saveObj = {};
            saveObj[`folscan_${username}_followers`] = currentFollowersMap;
            saveObj[`folscan_${username}_followings`] = currentFollowingsMap;
            saveObj[`folscan_${username}_report`] = { sections, timestamp };
            chrome.storage.local.set(saveObj);

            renderReportUI(username, sections, isPremium, timestamp);

            status.className = ""; status.innerText = "Done!";
            runBtn.disabled = false; dlBtn.disabled = false;
        });
    };

    updateDropdown();
    updatePremiumUI();

    // Periodically check premium status to update launcher crown if changed in popup
    setInterval(updatePremiumUI, 2000);

    runBtn.addEventListener("click", () => {
        const u = userInp.value.trim();
        if (u) { currentTarget = u; saveUserToHistory(u); runScan(u).catch(e => { status.innerText = "❌ " + e.message; runBtn.disabled = false; }); }
    });
    dlBtn.addEventListener("click", () => {
        chrome.storage.local.get([`folscan_${currentTarget}_followers`, `folscan_${currentTarget}_followings`], (data) => {
            const out = { followers: data[`folscan_${currentTarget}_followers`], followings: data[`folscan_${currentTarget}_followings`] };
            const blob = new Blob([JSON.stringify(out, null, 2)], {type: "application/json"});
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob); link.download = `${currentTarget}_scan.json`; link.click();
        });
    });
    resetBtn.addEventListener("click", () => {
        if (confirm("Clear history? (This will not affect your premium license)")) {
            chrome.storage.local.get(null, (items) => {
                const keysToRemove = Object.keys(items).filter(k => k.startsWith("folscan_"));
                chrome.storage.local.remove(keysToRemove, () => location.reload());
            });
        }
    });
    launcher.addEventListener("click", () => {
        popupDiv.style.display = "block";
        launcher.style.display = "none";
        updatePremiumUI();
    });
    closeBtn.addEventListener("click", () => {
        popupDiv.style.display = "none";
        launcher.style.display = "block";
    });
})();
