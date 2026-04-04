(async function() {
    if (document.getElementById("folscan-popup")) return;

    const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    const popup = document.createElement("div");
    popup.id = "folscan-popup-container";
    popup.innerHTML = `
    <style>
        #folscan-launcher { position: fixed; bottom: 20px; right: 20px; background: gold; color: black; font-weight: bold; padding: 10px 15px; border-radius: 50px; cursor: pointer; z-index: 100001; box-shadow: 0 4px 10px rgba(0,0,0,0.5); font-family: sans-serif; font-size: 14px; }
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
        .folscan-link { color: inherit; text-decoration: none; display: block; margin: 2px 0; font-size: 13px; }
        .folscan-link:hover { text-decoration: underline; }
        #premium-badge { color: gold; font-weight: bold; margin-left: 10px; display: none; }
    </style>
    <div id="folscan-launcher">FolScan</div>
    <div id="folscan-popup">
        <h2>FolScan <span id="premium-badge">PREMIUM</span></h2>
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

    const isPremium = async () => {
        const data = await chrome.storage.local.get("isPremium");
        return !!data.isPremium;
    };

    const updatePremiumUI = async () => {
        if (await isPremium()) {
            premiumBadge.style.display = "inline";
        } else {
            premiumBadge.style.display = "none";
        }
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
        if (userList.value) userInp.value = userList.value;
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

    const runScan = async (username) => {
        if (!isLoggedIn()) {
            throw new Error("You must be logged in to Instagram to run a scan.");
        }
        const premium = await isPremium();
        runBtn.disabled = true; dlBtn.disabled = true; status.className = "pulse";
        status.innerText = `Connecting...`;

        const searchRes = await safeFetch(`https://www.instagram.com/web/search/topsearch/?query=${username}`);
        const userId = searchRes.users.find(u => u.user.username === username)?.user.pk;
        if (!userId) throw new Error("User not found.");

        const fetchAll = async (type, hash) => {
            let results = [], cursor = null, hasNext = true, pageCount = 0;
            const maxFree = 100;
            while (hasNext) {
                if (!premium && results.length >= maxFree) {
                    status.innerText = `Free limit reached (${maxFree}). Upgrade for more!`;
                    break;
                }
                status.innerText = `Fetching ${type}: ${results.length}...`;
                const url = `https://www.instagram.com/graphql/query/?query_hash=${hash}&variables=` +
                            encodeURIComponent(JSON.stringify({ id: userId, first: 50, after: cursor }));
                const data = await safeFetch(url);
                const page = data.data.user[type];
                results = results.concat(page.edges.map(({ node: n }) => ({ username: n.username, full_name: n.full_name })));
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
            const lastFollowers = data[`folscan_${username}_followers`] || [];
            const lastFollowings = data[`folscan_${username}_followings`] || [];

            const sections = [
                { title: "🆕 New Followers", list: currentFollowers.filter(f => !lastFollowers.some(l => l.username === f.username)), color: "lightgreen", premium: false },
                { title: "❌ Lost Followers", list: lastFollowers.filter(l => !currentFollowers.some(f => f.username === l.username)), color: "salmon", premium: true },
                { title: "🆕 New Followings", list: currentFollowings.filter(f => !lastFollowings.some(l => l.username === f.username)), color: "lightblue", premium: false },
                { title: "📤 Unfollowed (By You)", list: lastFollowings.filter(l => !currentFollowings.some(f => f.username === l.username)), color: "#ff6b6b", premium: true },
                { title: "🚫 Not Following Back", list: currentFollowings.filter(f => !currentFollowers.some(c => c.username === f.username)), color: "orange", premium: false },
                { title: "🤝 Mutual", list: currentFollowings.filter(f => currentFollowers.some(c => c.username === f.username)), color: "#00d4ff", premium: false }
            ];

            // Sort: Items with lists > 0 come first
            sections.sort((a, b) => (b.list.length > 0) - (a.list.length > 0));

            const saveObj = {};
            saveObj[`folscan_${username}_followers`] = currentFollowers;
            saveObj[`folscan_${username}_followings`] = currentFollowings;
            chrome.storage.local.set(saveObj);

            report.innerHTML = "";
            const title = document.createElement("h3");
            title.textContent = `📊 Report for @${username}`;
            report.appendChild(title);

            if (!premium) {
                const limitMsg = document.createElement("p");
                limitMsg.style.color = "gold";
                limitMsg.textContent = "Limited to 100 items and basic reports for free users.";
                report.appendChild(limitMsg);
            }

            sections.forEach(s => {
                const isLocked = s.premium && !premium;
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
                    s.list.forEach(u => {
                        const a = document.createElement("a");
                        a.className = "folscan-link";
                        a.href = `https://instagram.com/${u.username}`;
                        a.target = "_blank";
                        a.textContent = `@${u.username} (${u.full_name || 'No Name'})`;
                        div.appendChild(a);
                    });
                }
                details.appendChild(div);
                report.appendChild(details);
            });

            status.className = ""; status.innerText = "Done!";
            runBtn.disabled = false; dlBtn.disabled = false;
        });
    };

    updateDropdown();
    updatePremiumUI();
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
    });
    closeBtn.addEventListener("click", () => {
        popupDiv.style.display = "none";
        launcher.style.display = "block";
    });
})();
