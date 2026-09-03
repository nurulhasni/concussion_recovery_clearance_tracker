<!-- Platform & Demo Guide Modal -->
<div id="guideModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md overflow-y-auto">
    <div class="soft-card rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-teal-500/30 animate-in fade-in zoom-in duration-150 overflow-hidden my-auto">
        
        <!-- Modal Header -->
        <div class="px-5 sm:px-6 py-4 border-b border-white/[0.08] flex items-center justify-between bg-slate-900/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-teal-500 to-cyan-400 p-0.5 shadow-md shadow-teal-500/20 flex items-center justify-center text-slate-950">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Platform & Demo Guide</span>
                        <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/40">
                            CDC & PedsConcussion
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400">Everything you need to know about the system features and presentation flow</p>
                </div>
            </div>

            <button type="button" 
                    onclick="document.getElementById('guideModal').classList.add('hidden')" 
                    class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition"
                    title="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="px-5 sm:px-6 py-5 space-y-6 overflow-y-auto text-xs sm:text-sm text-slate-300 leading-relaxed">

            <!-- Section 1: The Problem & Clinical Purpose -->
            <div class="rounded-xl border border-teal-500/20 bg-teal-950/20 p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-300 mb-1 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Clinical Purpose & Problem Solved
                </h3>
                <p class="text-slate-300 leading-relaxed text-xs">
                    Returning to sports or school prematurely after a youth concussion poses a lethal risk known as <strong>Second Impact Syndrome</strong>. Recovery information is traditionally siloed between parents, busy doctors, and schools. This platform unifies them through an evidence-based digital recovery passport, clinical AI safety checks, and multi-party sign-offs.
                </p>
            </div>

            <!-- Section 2: 3-Minute Quick Demo Cheat Sheet (3 Seeded Patients) -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        3-Minute Demo Cheat Sheet (How to Present Without Getting Tired)
                    </h3>
                    <span class="text-[11px] text-slate-400">Use the 3 pre-seeded accounts</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Alex Rivera -->
                    <div class="rounded-xl border border-white/[0.08] bg-slate-900/60 p-3.5 flex flex-col justify-between hover:border-teal-500/40 transition">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-white text-xs">1. Alex Rivera</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-500/40">Stage 1</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mb-2">
                                <strong>Recent Concussion (Day 3).</strong> Rest & cognitive pause.
                            </p>
                            <div class="text-[11px] text-teal-300/90 bg-teal-950/30 p-2 rounded-lg border border-teal-500/20 mb-3">
                                💡 <strong>Demo This:</strong> Click <em>Log Daily Symptoms</em> &rarr; pick <em>Mild / Improving</em> or <em>Red Flag</em> to show instant AI extraction, layperson summary, and automatic safety downgrade.
                            </div>
                        </div>
                    </div>

                    <!-- Jordan Taylor -->
                    <div class="rounded-xl border border-white/[0.08] bg-slate-900/60 p-3.5 flex flex-col justify-between hover:border-teal-500/40 transition">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-white text-xs">2. Jordan Taylor</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-teal-950 text-teal-300 border border-teal-500/40">Stage 2</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mb-2">
                                <strong>School Recovery (Day 8).</strong> Return-to-Learn stage.
                            </p>
                            <div class="text-[11px] text-teal-300/90 bg-teal-950/30 p-2 rounded-lg border border-teal-500/20 mb-3">
                                💡 <strong>Demo This:</strong> Scroll to the bottom panel to open the <em>Doctor</em> and <em>School</em> review portals without logging in, and test multi-party approvals!
                            </div>
                        </div>
                    </div>

                    <!-- Maya Chen -->
                    <div class="rounded-xl border border-white/[0.08] bg-slate-900/60 p-3.5 flex flex-col justify-between hover:border-teal-500/40 transition">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-white text-xs">3. Maya Chen</span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-500/40">Stage 3 &bull; Step 3</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mb-2">
                                <strong>Physical Progression (Day 10).</strong> CDC 6-Step Return-to-Play.
                            </p>
                            <div class="text-[11px] text-teal-300/90 bg-teal-950/30 p-2 rounded-lg border border-teal-500/20 mb-3">
                                💡 <strong>Demo This:</strong> Click the approver links to showcase the <strong>CDC 403 Safety Gate</strong> (Final Clearance is blocked before Step 6!). Then log 3 daily reports to reach Step 6 and unlock the portals!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: The 4 Milestones Journey -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    The 4 Milestone Clearance Framework
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06]">
                        <div class="font-bold text-teal-300 text-xs mb-0.5">Milestone 1: Initial Assessment</div>
                        <p class="text-[11px] text-slate-400">Medical baseline diagnosis & initial recovery plan (Rest & cognitive pause). Cleared by Doctor.</p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06]">
                        <div class="font-bold text-teal-300 text-xs mb-0.5">Milestone 2: School Recovery</div>
                        <p class="text-[11px] text-slate-400">Return-to-Learn academic accommodations & classroom rest protocols active. Cleared by Doctor + School.</p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06]">
                        <div class="font-bold text-teal-300 text-xs mb-0.5">Milestone 3: Graduated Physical Activity</div>
                        <p class="text-[11px] text-slate-400">CDC 6-Step Return-to-Play graduated physical progression active. Cleared by Doctor + School + Parent.</p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06]">
                        <div class="font-bold text-teal-300 text-xs mb-0.5">Milestone 4: Final Clearance</div>
                        <p class="text-[11px] text-slate-400">Full multidisciplinary medical & academic clearance verified. 100% authorized for competition.</p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Key Platform Features & Technical Architecture -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3 flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    3 Architectural Pillars
                </h3>
                <div class="space-y-2">
                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06] flex items-start gap-3">
                        <span class="text-base shrink-0">🤖</span>
                        <div>
                            <strong class="text-white text-xs block">AI Clinical Symptom Analyzer + Dual Guardrail</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Analyzes free-text reports using OpenAI/Gemini with negation awareness ("zero headache" stays mild). A secondary deterministic keyword scanner catches emergency danger signs (e.g. repeated vomiting, seizures) even if an AI false-negative occurs.
                            </p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06] flex items-start gap-3">
                        <span class="text-base shrink-0">🩺</span>
                        <div>
                            <strong class="text-white text-xs block">Multi-Party Consensus Gate</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Milestone advancement is strictly blocked until every designated stakeholder has independently voted "Approved". If any reviewer votes "Rejected", advancement is halted for safety.
                            </p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/70 border border-white/[0.06] flex items-start gap-3">
                        <span class="text-base shrink-0">🔒</span>
                        <div>
                            <strong class="text-white text-xs block">Frictionless 64-Character Token Portals</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Doctors and school officials don't need to register or remember passwords. Each milestone sends a secure, cryptographic single-use token valid for 7 days that gets consumed upon review.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-5 sm:px-6 py-3.5 border-t border-white/[0.08] bg-slate-900/80 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-slate-400">
                Tip: Switch language anytime via the top-right navbar menu.
            </span>
            <button type="button" 
                    onclick="document.getElementById('guideModal').classList.add('hidden')"
                    class="rounded-xl bg-teal-500 hover:bg-teal-400 px-4 py-2 text-xs font-bold text-slate-950 shadow transition">
                Got It / Close Guide
            </button>
        </div>

    </div>
</div>
