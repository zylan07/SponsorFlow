-- ====================================================================
-- SponsorFlow - Realistic Seed Data
-- ====================================================================

-- --------------------------------------------------------------------
-- 1. users
-- Passwords:
-- admin@sponsorflow.local    => Admin@123
-- manager@sponsorflow.local  => Manager@123
-- staff@sponsorflow.local    => Staff@123
-- --------------------------------------------------------------------
INSERT INTO users (id, name, email, password_hash, role) VALUES
(1, 'System Administrator', 'admin@sponsorflow.local', '$2y$10$x6KZj6GzsH7F.fnM.gLIFewz/DzeGraP.QBJ1Dz9ZrSTttqunBJta', 'admin'),
(2, 'Aditi Sharma', 'manager@sponsorflow.local', '$2y$10$fnqTF772h9soSeYZVjXN5uMzppNljDQ5wNiCdlPzYwiQil/CfDV4q', 'manager'),
(3, 'Ravi Teja', 'staff@sponsorflow.local', '$2y$10$tHQFWGwdXj.LSlaIdPabUerZRHNtSCoUXVKN42ry3xRwfsPNazFXG', 'staff');

-- --------------------------------------------------------------------
-- 2. companies
-- --------------------------------------------------------------------
INSERT INTO companies (id, company_name, industry, location, website, phone, email, status, estimated_value, notes) VALUES
(1, 'NovaGrid Technologies', 'IT Services', 'Coimbatore', 'www.novagrid.example', '9840107919', 'partnerships@novagrid.example', 'Confirmed', 50000.00, 'Introduced through the college alumni network. Committed Gold Sponsor.'),
(2, 'Vertex Systems', 'IT Services', 'Chennai', 'www.vertexsystems.example', '9840115838', 'alliances@vertexsystems.example', 'Negotiation', 35000.00, 'Sponsored a similar symposium last year; reviewing package deliverables.'),
(3, 'BlueOrbit Labs', 'Data & AI', 'Bengaluru', 'www.blueorbitlabs.example', '9840123757', 'community@blueorbitlabs.example', 'Confirmed', 40000.00, 'Prefers email; replies within two days. Silver Sponsor with AI demo slot.'),
(4, 'NexaWorks', 'Cloud', 'Hyderabad', 'www.nexaworks.example', '9840131676', 'brand@nexaworks.example', 'Confirmed', 30000.00, 'Interested in workshop slot for cloud architecture hiring.'),
(5, 'QuantumEdge Solutions', 'Data & AI', 'Pune', 'www.quantumedgesolutions.example', '9840139595', 'outreach@quantumedge.example', 'Confirmed', 75000.00, 'Title Sponsor. Exclusive keynote and chief guest felicitation arranged.'),
(6, 'CodeForge Technologies', 'IT Services', 'Coimbatore', 'www.codeforge.example', '9840147514', 'hr@codeforge.example', 'Contacted', 0.00, 'Sent introductory brochure. Awaiting initial callback.'),
(7, 'InnoSphere Learning', 'Education', 'Madurai', 'www.innosphere.example', '9840155433', 'partners@innosphere.example', 'Prospect', 0.00, 'Identified via regional ed-tech directory; deck to be shared.'),
(8, 'CloudAxis Networks', 'Cloud', 'Chennai', 'www.cloudaxis.example', '9840163352', 'sales@cloudaxis.example', 'Interested', 20000.00, 'Requested customized Bronze package for developer relations.'),
(9, 'DataNest Analytics', 'Data & AI', 'Kochi', 'www.datanest.example', '9840171271', 'marketing@datanest.example', 'Confirmed', 25000.00, 'Bronze Sponsor. Branding on hackathon kit approved.'),
(10, 'TechBridge Solutions', 'IT Services', 'Bengaluru', 'www.techbridge.example', '9840179190', 'info@techbridge.example', 'Rejected', 0.00, 'Annual marketing budget already allocated for current fiscal year.'),
(11, 'FinLeaf Payments', 'Fintech', 'Mumbai', 'www.finleaf.example', '9840187109', 'csr@finleaf.example', 'Confirmed', 60000.00, 'CSR FinTech partner with dedicated women-in-tech prize sponsorship.'),
(12, 'GreenCart Retail', 'Retail', 'Coimbatore', 'www.greencart.example', '9840195028', 'promo@greencart.example', 'Contacted', 0.00, 'First call completed; requested formal deck over email.'),
(13, 'SteelAnvil Industries', 'Manufacturing', 'Salem', 'www.steelanvil.example', '9840202947', 'pr@steelanvil.example', 'Prospect', 0.00, 'Potential regional partner for mechanical track competitions.'),
(14, 'LearnLoop Academy', 'Education', 'Trichy', 'www.learnloop.example', '9840210866', 'founder@learnloop.example', 'Confirmed', 20000.00, 'Community Partner; free student coding passes bundled in tier.'),
(15, 'PixelHarbor Studio', 'IT Services', 'Chennai', 'www.pixelharbor.example', '9840218785', 'contact@pixelharbor.example', 'Prospect', 0.00, 'New creative design lead identified on LinkedIn.'),
(16, 'AquaByte Systems', 'Cloud', 'Coimbatore', 'www.aquabyte.example', '9840226704', 'business@aquabyte.example', 'Proposal Sent', 28000.00, 'Proposal P-106 sent with custom banner & stall options.'),
(17, 'OrbitPay Global', 'Fintech', 'Bengaluru', 'www.orbitpay.example', '9840234623', 'brand@orbitpay.example', 'Proposal Sent', 45000.00, 'Proposal P-107 sent; team evaluating Gold vs Silver package.'),
(18, 'BrightPath Learning', 'Education', 'Chennai', 'www.brightpath.example', '9840242542', 'director@brightpath.example', 'Confirmed', 15000.00, 'Internship Partner; hiring booth on day 2 of event.'),
(19, 'ForgeWorks Engineering', 'Manufacturing', 'Coimbatore', 'www.forgeworks.example', '9840250461', 'gm@forgeworks.example', 'Confirmed', 35000.00, 'Silver Sponsor. Sponsoring hardware hackathon showcase.'),
(20, 'CartWheel Retail', 'Retail', 'Kochi', 'www.cartwheel.example', '9840258380', 'marketing@cartwheel.example', 'Interested', 18000.00, 'Asked for stall pricing and campus footfall demographic breakdown.'),
(21, 'SignalStack Systems', 'IT Services', 'Hyderabad', 'www.signalstack.example', '9840266299', 'devrel@signalstack.example', 'Confirmed', 22000.00, 'Technology Partner; API challenge track prizes provided.'),
(22, 'HelioCloud Computing', 'Cloud', 'Pune', 'www.heliocloud.example', '9840274218', 'alliances@heliocloud.example', 'Negotiation', 50000.00, 'Discussing custom workshop and keynote additions to Gold tier.'),
(23, 'MetricMint Labs', 'Data & AI', 'Chennai', 'www.metricmint.example', '9840282137', 'growth@metricmint.example', 'Contacted', 0.00, 'Reached out to VP Engineering on LinkedIn, requested follow-up.'),
(24, 'UrbanLoom Textiles', 'Retail', 'Tiruppur', 'www.urbanloom.example', '9840290056', 'procure@urbanloom.example', 'Rejected', 0.00, 'Decision makers unable to sponsor non-textile events this season.');

-- --------------------------------------------------------------------
-- 3. contacts
-- --------------------------------------------------------------------
INSERT INTO contacts (id, company_id, name, designation, email, phone, preferred_contact, relationship_status, last_contacted, notes) VALUES
(1, 1, 'Anita Rao', 'Head of Partnerships', 'anita@novagrid.example', '9840107919', 'Email', 'Strategic', '2026-09-28', 'Primary decision maker; very supportive of student hackathons.'),
(2, 2, 'Karthik S', 'Marketing Manager', 'karthik@vertexsystems.example', '9840115838', 'Phone Call', 'Engaged', '2026-10-01', 'Handling branding and stall coordination.'),
(3, 3, 'Priya Menon', 'Community Lead', 'priya@blueorbitlabs.example', '9840123757', 'WhatsApp', 'Strategic', '2026-10-03', 'Coordinating AI demo session and student mentors.'),
(4, 4, 'Rahul Verma', 'Brand Manager', 'rahul@nexaworks.example', '9840131676', 'Email', 'Strategic', '2026-09-30', 'Managing banner creative assets and team badge list.'),
(5, 5, 'Sneha Iyer', 'Director, Outreach', 'sneha@quantumedgesolutions.example', '9840139595', 'Meeting', 'Strategic', '2026-10-05', 'Direct contact for Title Sponsorship contract and schedule.'),
(6, 6, 'Vikram N', 'HR Manager', 'vikram@codeforge.example', '9840147514', 'LinkedIn', 'Warm', '2026-10-02', 'Evaluating talent acquisition stall availability.'),
(7, 7, 'Divya P', 'Program Coordinator', 'divya@innosphere.example', '9840155433', 'Email', 'New', NULL, 'Contacted via alumni reference.'),
(8, 8, 'Arjun K', 'Sales Head', 'arjun@cloudaxis.example', '9840163352', 'Phone Call', 'Warm', '2026-10-04', 'Seeking cloud credits giveaway for attendees.'),
(9, 9, 'Meera J', 'Marketing Lead', 'meera@datanest.example', '9840171271', 'Email', 'Strategic', '2026-09-29', 'Supervising logo kit and social collateral submissions.'),
(10, 10, 'Suresh B', 'Partnerships Manager', 'suresh@techbridge.example', '9840179190', 'Email', 'Inactive', '2026-09-20', 'Requested keeping in touch for the upcoming summer event.'),
(11, 11, 'Ritu Shah', 'CSR Head', 'ritu@finleaf.example', '9840187109', 'Meeting', 'Strategic', '2026-10-02', 'In charge of CSR approval and disbursement paperwork.'),
(12, 12, 'Naveen T', 'Marketing Executive', 'naveen@greencart.example', '9840195028', 'Phone Call', 'Warm', '2026-10-04', 'Forwarded proposal to head of growth for signoff.'),
(13, 13, 'Hari M', 'PR Officer', 'hari@steelanvil.example', '9840202947', 'Email', 'New', NULL, 'Awaiting introductory meeting schedule.'),
(14, 14, 'Pooja R', 'Founder', 'pooja@learnloop.example', '9840210866', 'WhatsApp', 'Strategic', '2026-09-27', 'Approved partnership during preliminary virtual call.'),
(15, 15, 'Dev A', 'Creative Director', 'dev@pixelharbor.example', '9840218785', 'LinkedIn', 'New', NULL, 'Reached out via direct message regarding design sponsorship.'),
(16, 16, 'Isha G', 'Business Head', 'isha@aquabyte.example', '9840226704', 'Email', 'Engaged', '2026-10-03', 'Reviewing Proposal P-106 with technical leadership.'),
(17, 17, 'Manoj L', 'Brand Lead', 'manoj@orbitpay.example', '9840234623', 'Email', 'Engaged', '2026-10-04', 'Awaiting legal team clearance on sponsorship agreement clauses.'),
(18, 18, 'Latha S', 'Director', 'latha@brightpath.example', '9840242542', 'Phone Call', 'Strategic', '2026-09-29', 'Confirmed placement drive partnership and stall placement.'),
(19, 19, 'Rajesh P', 'General Manager', 'rajesh@forgeworks.example', '9840250461', 'Meeting', 'Strategic', '2026-10-02', 'Finalized manufacturing track challenge and prizes.'),
(20, 20, 'Nisha V', 'Marketing Head', 'nisha@cartwheel.example', '9840258380', 'Email', 'Warm', '2026-10-03', 'Interested in distributing discount vouchers inside delegate kit.'),
(21, 21, 'Imran Q', 'Developer Relations', 'imran@signalstack.example', '9840266299', 'LinkedIn', 'Strategic', '2026-10-01', 'Handling API workshop scheduling and hackathon mentors.'),
(22, 22, 'Tara D', 'Alliances Manager', 'tara@heliocloud.example', '9840274218', 'Meeting', 'Engaged', '2026-10-05', 'Negotiating second workshop slot and VIP dinner invites.'),
(23, 23, 'Gokul R', 'Growth Manager', 'gokul@metricmint.example', '9840282137', 'Phone Call', 'Warm', '2026-10-02', 'Requested presentation deck regarding footfall demographics.'),
(24, 24, 'Sana K', 'Procurement Head', 'sana@urbanloom.example', '9840290056', 'Email', 'Inactive', '2026-09-18', 'Budget closed for this quarter.'),
(25, 1, 'Mahesh Kumar', 'Technical Lead', 'mahesh@novagrid.example', '9841205113', 'Email', 'Engaged', '2026-09-25', 'Secondary contact for live demonstration setup.'),
(26, 5, 'Leena Thomas', 'Events Coordinator', 'leena@quantumedge.example', '9841210226', 'Phone Call', 'Strategic', '2026-10-04', 'Handles on-ground logistics for Title Sponsor lounge.'),
(27, 11, 'Arvind Nair', 'Finance Manager', 'arvind@finleaf.example', '9841215339', 'Email', 'Engaged', '2026-10-03', 'Point person for invoice processing and GST compliance.'),
(28, 3, 'Fatima Noor', 'Operations Lead', 'fatima@blueorbitlabs.example', '9841220452', 'Email', 'Engaged', '2026-09-26', 'Coordinates stage banner sizing and resolution checks.'),
(29, 4, 'Gautam R', 'Finance Executive', 'gautam@nexaworks.example', '9841225565', 'Email', 'Engaged', '2026-09-29', 'Processed first instalment payment transfer.'),
(30, 2, 'Bhavna T', 'Procurement Manager', 'bhavna@vertexsystems.example', '9841230678', 'Email', 'Engaged', '2026-10-02', 'Reviewing standard vendor vendor onboarding forms.');

-- --------------------------------------------------------------------
-- 4. outreach
-- --------------------------------------------------------------------
INSERT INTO outreach (id, company_id, contact_id, outreach_type, subject, message, outreach_date, status, response, assigned_to, next_action) VALUES
(1, 1, 1, 'Email', 'Sponsorship Invitation for TechNova 2027', 'Introductory pitch detailing past edition attendance and sponsorship tiers.', '2026-09-10', 'Responded', 'Replied with excitement and requested tier breakdown.', 'Aditi Sharma', 'Share formal Gold proposal'),
(2, 1, 1, 'Meeting', 'Review Gold Package Deliverables', 'Reviewed logo branding, main stage backdrop, and dedicated recruitment stall.', '2026-09-18', 'Interested', 'Approved deliverables list; requested formal agreement.', 'Aditi Sharma', 'Prepare sponsorship contract'),
(3, 2, 2, 'Email', 'TechNova 2027 Sponsorship Collaboration', 'Shared prospectus with focus on developer talent access.', '2026-09-12', 'Responded', 'Expressed interest in Silver package with minor customization.', 'Ravi Teja', 'Schedule negotiation call'),
(4, 2, 2, 'Phone Call', 'Follow-up on Silver Tier Pricing', 'Discussed booth location adjustments and speaking slot duration.', '2026-09-22', 'Interested', 'Agreed to ₹35,000 pending executive signoff.', 'Ravi Teja', 'Send updated contract draft'),
(5, 3, 3, 'Email', 'Invitation: Silver Partner for AI Track', 'Proposed AI hackathon title rights and jury seat.', '2026-09-14', 'Responded', 'Interested in mentoring students; requested invoice procedure.', 'Aditi Sharma', 'Send invoice for 50% advance'),
(6, 4, 4, 'Email', 'Cloud Infrastructure Partnership Invitation', 'Invited NexaWorks as Cloud Sponsor with workshop hall naming rights.', '2026-09-11', 'Responded', 'Welcomed proposal; forwarded to brand committee.', 'Ravi Teja', 'Follow up on committee decision'),
(7, 4, 4, 'WhatsApp', 'Reminder on TechNova Cloud Track', 'Sent quick reminder regarding early bird stall reservation.', '2026-09-20', 'Interested', 'Confirmed participation; asked for payment portal link.', 'Ravi Teja', 'Share bank transfer details'),
(8, 5, 5, 'Meeting', 'Executive Title Sponsorship Presentation', 'In-person meeting at Pune HQ demonstrating footfall of 3,500+ attendees.', '2026-09-15', 'Interested', 'Agreed to ₹75,000 Title Sponsorship package.', 'System Administrator', 'Execute bilateral agreement'),
(9, 6, 6, 'Email', 'Campus Talent & Sponsorship Invitation', 'Shared student profile distribution and hiring desk details.', '2026-09-24', 'Delivered', 'Email opened by recipient; awaiting response.', 'Aditi Sharma', 'Follow up via phone call'),
(10, 7, 7, 'Email', 'TechNova Education Partner Opportunity', 'Sent invitation deck and syllabus tie-in details.', '2026-09-26', 'Sent', NULL, 'Ravi Teja', 'First phone touchpoint'),
(11, 8, 8, 'Phone Call', 'Exploring CloudAxis Campus Presence', 'Call with Sales Head discussing developer outreach.', '2026-09-27', 'Responded', 'Requested Bronze package deck with booth map.', 'Aditi Sharma', 'Send customized Bronze deck'),
(12, 9, 9, 'Email', 'Data & AI Track Bronze Partner Invitation', 'Sent proposal outlining branding across hackathon kits.', '2026-09-15', 'Interested', 'Accepted proposal terms via reply email.', 'Ravi Teja', 'Collect company high-res logo'),
(13, 10, 10, 'Email', 'TechBridge Partnership Proposal', 'Shared standard sponsorship packages and tier benefits.', '2026-09-10', 'Not Interested', 'Declined due to budget freeze this semester.', 'Aditi Sharma', 'Archive lead for next year'),
(14, 11, 11, 'Email', 'FinLeaf Women in Tech CSR Partnership', 'Proposed sponsored merit scholarships and keynote presentation.', '2026-09-16', 'Responded', 'Requested formal CSR proposal with tax exemption details.', 'System Administrator', 'Submit formal proposal P-105'),
(15, 11, 11, 'Meeting', 'CSR Committee Review Call', 'Virtual review with Mumbai CSR committee members.', '2026-09-25', 'Interested', 'Approved grant of ₹60,000.', 'System Administrator', 'Process agreement and invoice'),
(16, 12, 12, 'Phone Call', 'GreenCart Retail Brand Presence at TechNova', 'Discussed retail coupon distribution to student attendees.', '2026-09-29', 'Responded', 'Requested email summary for senior management.', 'Ravi Teja', 'Send formal email recap'),
(17, 13, 13, 'Email', 'Manufacturing Track Innovation Sponsorship', 'Invited regional heavy industry participation.', '2026-09-28', 'Sent', NULL, 'Aditi Sharma', 'Schedule follow-up call'),
(18, 14, 14, 'WhatsApp', 'LearnLoop Academy Community Partner Discussion', 'Shared event brochure and student community outreach numbers.', '2026-09-17', 'Interested', 'Immediate buy-in; agreed to ₹20,000 tier.', 'Ravi Teja', 'Send onboarding checklist'),
(19, 15, 15, 'LinkedIn', 'Design Track Mentorship & Sponsorship', 'InMail sent to Creative Director regarding branding.', '2026-09-30', 'Sent', NULL, 'Aditi Sharma', 'Follow up after 4 days'),
(20, 16, 16, 'Email', 'AquaByte Cloud Track Sponsorship Deck', 'Shared Proposal P-106 detailing ₹28,000 tier.', '2026-09-23', 'Responded', 'Positive feedback; reviewing budget availability.', 'Ravi Teja', 'Follow up on P-106 acceptance'),
(21, 17, 17, 'Email', 'OrbitPay Fintech Partner Proposal P-107', 'Sent customized proposal for ₹45,000 Silver tier.', '2026-09-22', 'Responded', 'Evaluating booth specifications and VIP networking passes.', 'Aditi Sharma', 'Clarify booth power & internet amenities'),
(22, 18, 18, 'Phone Call', 'BrightPath Internship Drive Partnership', 'Discussed exclusive recruitment desk on campus.', '2026-09-19', 'Interested', 'Confirmed internship partner fee of ₹15,000.', 'Ravi Teja', 'Issue invoice and receipt'),
(23, 19, 19, 'Meeting', 'ForgeWorks Hardware Hackathon Partnership', 'Reviewed hardware lab visits and student prototype demos.', '2026-09-21', 'Interested', 'Confirmed ₹35,000 Silver tier.', 'Aditi Sharma', 'Share deliverables calendar'),
(24, 20, 20, 'Email', 'CartWheel Retail Student Reach Campaign', 'Sent demographic statistics of attendees and gift bag inclusion rates.', '2026-09-28', 'Responded', 'Asked for lower pricing tier options.', 'Ravi Teja', 'Send revised custom tier'),
(25, 21, 21, 'LinkedIn', 'SignalStack API Challenge Sponsorship', 'Initiated conversation on sponsoring Developer Relations prize pool.', '2026-09-19', 'Interested', 'Agreed to ₹22,000 Technology Partner tier.', 'Aditi Sharma', 'Finalize API problem statement'),
(26, 22, 22, 'Meeting', 'HelioCloud Executive Alliance Meeting', 'Detailed discussion on Gold tier and Cloud Computing keynote.', '2026-09-26', 'Interested', 'Proposed ₹50,000 package pending small perks.', 'System Administrator', 'Close revised proposal terms'),
(27, 23, 23, 'Phone Call', 'MetricMint Growth Partnerships Outreach', 'Spoke to growth manager about analytics workshop.', '2026-10-01', 'Delivered', 'Gatekeeper took message; callback requested.', 'Ravi Teja', 'Retry phone outreach'),
(28, 24, 24, 'Email', 'UrbanLoom Textile Event Support', 'Invited corporate merchandise tie-up.', '2026-09-12', 'Not Interested', 'Reply: non-relevant event vertical.', 'Aditi Sharma', 'Mark lead closed-lost'),
(29, 1, 1, 'WhatsApp', 'TechNova Logo Assets Checklist', 'Shared dimensions for vector logo on main backdrop.', '2026-10-02', 'Responded', 'Delivered SVG files and brand guidelines.', 'Aditi Sharma', 'Send to stage printer'),
(30, 5, 26, 'Phone Call', 'VIP Speaker Logistics Call', 'Coordinated hotel accommodation and arrival timing for Dr. Sneha Iyer.', '2026-10-04', 'Responded', 'Flight itinerary confirmed and logged.', 'System Administrator', 'Arrange airport escort'),
(31, 2, 2, 'Email', 'Updated Proposal for Vertex Systems', 'Delivered revised P-102 proposal with updated workshop timing.', '2026-10-03', 'Responded', 'Under final review with regional finance team.', 'Ravi Teja', 'Await signed acceptance'),
(32, 11, 27, 'Email', 'GST Invoice and Bank Verification', 'Sent signed institutional invoice and cancel cheque copy for CSR audit.', '2026-10-04', 'Responded', 'Finance confirmed disbursement scheduled for this week.', 'System Administrator', 'Track bank clearance'),
(33, 14, 14, 'Email', 'Student Passes Voucher Codes', 'Dispatched promo coupons for 50 student participants.', '2026-10-03', 'Responded', 'Acknowledged receipt with thanks.', 'Ravi Teja', 'Monitor code redemptions');

-- --------------------------------------------------------------------
-- 5. followups
-- --------------------------------------------------------------------
INSERT INTO followups (id, company_id, contact_id, followup_date, priority, reason, status, assigned_to, notes) VALUES
(1, 1, 1, '2026-10-08', 'High', 'Confirm final payment schedule & collect speaker headshot', 'Pending', 'Aditi Sharma', 'Speaker profile required for official schedule brochure.'),
(2, 2, 2, '2026-10-07', 'High', 'Discuss revised contract terms & booth location', 'Pending', 'Ravi Teja', 'Needs final confirmation on stall dimensions (10x10 ft).'),
(3, 3, 3, '2026-10-12', 'Medium', 'Verify stage backdrop logo placement proof', 'Pending', 'Aditi Sharma', 'Share flex banner printer digital proof before printing.'),
(4, 4, 4, '2026-10-09', 'Medium', 'Confirm remaining 50% payment transfer', 'Pending', 'Ravi Teja', 'First instalment paid; final due before opening day.'),
(5, 5, 5, '2026-10-06', 'High', 'Finalize VIP seating plan and keynote tech check', 'Overdue', 'System Administrator', 'Urgent coordination needed with stage AV team.'),
(6, 6, 6, '2026-10-10', 'Medium', 'Follow up on recruitment desk interest', 'Pending', 'Aditi Sharma', 'Call HR lead to check if booth will be needed.'),
(7, 7, 7, '2026-10-15', 'Low', 'Send introductory prospectus deck', 'Pending', 'Ravi Teja', 'Alumni contact indicated interest in early November.'),
(8, 8, 8, '2026-10-08', 'High', 'Share customized Bronze package proposal', 'Pending', 'Aditi Sharma', 'Include cloud credits distribution details.'),
(9, 9, 9, '2026-10-05', 'Medium', 'Confirm receipt of hackathon starter kits', 'Overdue', 'Ravi Teja', 'Shipped via courier; tracking delivery status.'),
(10, 11, 11, '2026-10-11', 'High', 'Obtain signed CSR grant sanction letter', 'Pending', 'System Administrator', 'Required by university accounts office for disbursement.'),
(11, 12, 12, '2026-10-14', 'Low', 'Call back marketing lead regarding student gift coupons', 'Pending', 'Ravi Teja', 'Follow up after marketing committee meets on Monday.'),
(12, 13, 13, '2026-10-18', 'Low', 'Follow up on manufacturing track partnership', 'Pending', 'Aditi Sharma', 'Follow up after regional trade expo.'),
(13, 14, 14, '2026-10-04', 'Medium', 'Confirm student workshop registration numbers', 'Completed', 'Ravi Teja', 'Successfully verified 50 enrolled student attendees.'),
(14, 16, 16, '2026-10-07', 'High', 'Ask for feedback on Proposal P-106', 'Pending', 'Ravi Teja', 'Valid until Oct 23. Key prospect for cloud tier.'),
(15, 17, 17, '2026-10-09', 'High', 'Review OrbitPay proposal P-107 acceptance status', 'Pending', 'Aditi Sharma', 'Legal counsel reviewing clause 4 on logo indemnity.'),
(16, 18, 18, '2026-10-02', 'Medium', 'Collect internship job description flyers', 'Completed', 'Ravi Teja', 'Flyers received for print shop collation.'),
(17, 19, 19, '2026-10-13', 'Medium', 'Coordinate hardware competition component list', 'Pending', 'Aditi Sharma', 'Hardware kits list provided by university lab.'),
(18, 20, 20, '2026-10-16', 'Low', 'Send discounted student voucher distribution contract', 'Pending', 'Ravi Teja', 'Preparing draft for ₹18,000 tier.'),
(19, 21, 21, '2026-10-03', 'Medium', 'Confirm API challenge portal credentials', 'Completed', 'Aditi Sharma', 'Portal access test successful.'),
(20, 22, 22, '2026-10-08', 'High', 'Close HelioCloud negotiation & issue P-112', 'Pending', 'System Administrator', 'Agreed in principle on ₹50,000 Gold tier.'),
(21, 23, 23, '2026-10-11', 'Low', 'Follow-up call with Growth Manager Gokul', 'Pending', 'Ravi Teja', 'Second call scheduled for 3 PM.'),
(22, 1, 25, '2026-10-14', 'Medium', 'Verify demo stage internet bandwidth setup', 'Pending', 'Aditi Sharma', 'Arrange dedicated 100 Mbps LAN drop.');

-- --------------------------------------------------------------------
-- 6. proposals
-- --------------------------------------------------------------------
INSERT INTO proposals (id, proposal_number, company_id, contact_id, package_name, amount, sent_date, valid_until, status, benefits, notes) VALUES
(1, 'P-101', 1, 1, 'Gold Sponsor Package', 50000.00, '2026-09-12', '2026-10-12', 'Accepted', 'Prominent logo on stage backdrop, 10x10 ft stall, 5 VIP dinner passes, workshop slot, student resume access.', 'Accepted and converted to Sponsorship #1.'),
(2, 'P-102', 2, 2, 'Silver Sponsor Package', 35000.00, '2026-09-20', '2026-10-20', 'Negotiation', 'Logo on banners, 8x8 ft stall, 3 VIP passes, hackathon challenge naming right.', 'Revising booth orientation and electric hookup specs.'),
(3, 'P-103', 3, 3, 'Silver Sponsor Package', 40000.00, '2026-09-15', '2026-10-15', 'Accepted', 'Title branding for AI track, keynote address, 4 VIP passes, logo on event badges.', 'Accepted and converted to Sponsorship #2.'),
(4, 'P-104', 4, 4, 'Cloud Partner Package', 30000.00, '2026-09-16', '2026-10-16', 'Accepted', 'Naming rights to Cloud Hack lab, logo on website, 2 banner placements.', 'Accepted and converted to Sponsorship #3.'),
(5, 'P-105', 5, 5, 'Title Sponsor Package', 75000.00, '2026-09-15', '2026-10-15', 'Accepted', 'Title naming rights "TechNova powered by QuantumEdge", main stage backdrop center logo, 15-min keynote, 10 VIP passes.', 'Accepted and converted to Sponsorship #4.'),
(6, 'P-106', 16, 16, 'Technology Partner Package', 28000.00, '2026-09-23', '2026-10-23', 'Sent', 'Stall in exhibition hall, logo on promo videos, 2 delegate passes.', 'Sent by email; client reviewing internally.'),
(7, 'P-107', 17, 17, 'Silver Sponsor Package', 45000.00, '2026-09-22', '2026-10-22', 'Viewed', 'Fintech hackathon challenge co-branding, logo on registration portal, exhibition desk.', 'Viewed by brand head; pending committee call.'),
(8, 'P-108', 9, 9, 'Bronze Sponsor Package', 25000.00, '2026-09-18', '2026-10-18', 'Accepted', 'Logo on kits and certificates, standee in seminar hall.', 'Accepted and converted to Sponsorship #5.'),
(9, 'P-109', 10, 10, 'Silver Sponsor Package', 35000.00, '2026-09-10', '2026-09-30', 'Rejected', 'Standard silver entitlements.', 'Rejected due to fiscal budget closure.'),
(10, 'P-110', 11, 11, 'Gold Sponsor Package', 60000.00, '2026-09-19', '2026-10-19', 'Accepted', 'CSR scholarship branding, main stage presentation, student outreach access.', 'Accepted and converted to Sponsorship #6.'),
(11, 'P-111', 14, 14, 'Community Partner Package', 20000.00, '2026-09-18', '2026-10-18', 'Accepted', 'Coding pass sponsor, digital branding across livestreams.', 'Accepted and converted to Sponsorship #7.'),
(12, 'P-112', 22, 22, 'Gold Sponsor Package', 50000.00, '2026-09-27', '2026-10-27', 'Negotiation', 'Workshop naming rights, 10x10 ft booth, panel moderation seat.', 'Client requested second mentor pass in draft agreement.'),
(13, 'P-113', 18, 18, 'Internship Partner Package', 15000.00, '2026-09-20', '2026-10-20', 'Accepted', 'Dedicated recruitment lounge, logo on placement brochure.', 'Accepted and converted to Sponsorship #8.'),
(14, 'P-114', 19, 19, 'Silver Sponsor Package', 35000.00, '2026-09-22', '2026-10-22', 'Accepted', 'Hardware expo stall, jury seat in maker fair, logo on kit bags.', 'Accepted and converted to Sponsorship #9.'),
(15, 'P-115', 21, 21, 'Technology Partner Package', 22000.00, '2026-09-20', '2026-10-20', 'Accepted', 'API challenge branding, digital certificate logo.', 'Accepted and converted to Sponsorship #10.'),
(16, 'P-116', 8, 8, 'Bronze Sponsor Package', 20000.00, '2026-10-01', '2026-11-01', 'Draft', 'Cloud workshop naming, stall in outer corridor.', 'Drafting tier specifics for Sales Head Arjun.'),
(17, 'P-117', 20, 20, 'Bronze Sponsor Package', 18000.00, '2026-10-02', '2026-11-02', 'Draft', 'Voucher distribution inside delegate kit, roll-up banner.', 'Draft in preparation with customized coupon inserts.'),
(18, 'P-118', 24, 24, 'Bronze Sponsor Package', 20000.00, '2026-09-12', '2026-09-25', 'Expired', 'Standard bronze tier.', 'Expired without response.');

-- --------------------------------------------------------------------
-- 7. sponsorships
-- --------------------------------------------------------------------
INSERT INTO sponsorships (id, company_id, proposal_id, package_name, sponsorship_type, amount, start_date, end_date, payment_status, contract_status, overall_status) VALUES
(1, 1, 1, 'Gold Sponsor Package', 'Gold Sponsor', 50000.00, '2026-09-15', '2026-11-30', 'Paid', 'Signed', 'Active'),
(2, 3, 3, 'Silver Sponsor Package', 'Silver Sponsor', 40000.00, '2026-09-18', '2026-11-30', 'Partial', 'Signed', 'Active'),
(3, 4, 4, 'Cloud Partner Package', 'Technology Partner', 30000.00, '2026-09-20', '2026-11-30', 'Paid', 'Signed', 'Active'),
(4, 5, 5, 'Title Sponsor Package', 'Title Sponsor', 75000.00, '2026-09-20', '2026-12-15', 'Partial', 'Signed', 'Active'),
(5, 9, 8, 'Bronze Sponsor Package', 'Bronze Sponsor', 25000.00, '2026-09-22', '2026-11-30', 'Paid', 'Signed', 'Active'),
(6, 11, 10, 'CSR FinTech Partner', 'Gold Sponsor', 60000.00, '2026-09-25', '2026-12-31', 'Pending', 'Sent', 'Active'),
(7, 14, 11, 'Community Partner Package', 'Community Partner', 20000.00, '2026-09-22', '2026-11-30', 'Paid', 'Signed', 'Active'),
(8, 18, 13, 'Internship Partner Package', 'Internship Partner', 15000.00, '2026-09-25', '2026-11-30', 'Paid', 'Signed', 'Active'),
(9, 19, 14, 'Hardware Track Silver Partner', 'Silver Sponsor', 35000.00, '2026-09-26', '2026-11-30', 'Partial', 'Draft', 'Active'),
(10, 21, 15, 'Developer Relations Partner', 'Technology Partner', 22000.00, '2026-09-25', '2026-11-30', 'Overdue', 'Draft', 'Active'),
(11, 2, 2, 'Silver Sponsor Package', 'Silver Sponsor', 35000.00, '2026-10-01', '2026-11-30', 'Pending', 'Draft', 'On Hold');

-- --------------------------------------------------------------------
-- 8. deliverables
-- --------------------------------------------------------------------
INSERT INTO deliverables (id, sponsorship_id, company_id, deliverable, event_name, due_date, owner, priority, status, completion_date, notes) VALUES
(1, 1, 1, 'Logo placement on website header & partner section', 'TechNova 2027', '2026-09-25', 'Aditi Sharma', 'High', 'Completed', '2026-09-24', 'Live on staging and production websites.'),
(2, 1, 1, '10x10 ft Stall allocation in Main Hall', 'TechNova 2027', '2026-10-18', 'Ravi Teja', 'High', 'In Progress', NULL, 'Stall #A-04 demarcated on venue layout plan.'),
(3, 1, 1, 'Social media introduction banner & LinkedIn tag', 'TechNova 2027', '2026-09-28', 'Aditi Sharma', 'Medium', 'Completed', '2026-09-28', 'Published on official Instagram & LinkedIn pages.'),
(4, 2, 3, 'AI Track Keynote speaker slot reservation', 'TechNova 2027', '2026-10-15', 'System Administrator', 'High', 'Completed', '2026-10-02', 'Confirmed 45-minute slot in Auditorium B.'),
(5, 2, 3, 'Logo on participant identity badges and lanyards', 'TechNova 2027', '2026-10-10', 'Ravi Teja', 'High', 'In Progress', NULL, 'Print files dispatched to lanyard vendor.'),
(6, 2, 3, 'Hackathon problem statement review & mentor pass', 'TechNova 2027', '2026-10-08', 'Aditi Sharma', 'Medium', 'Completed', '2026-10-04', '3 mentor passes approved and sent to Priya Menon.'),
(7, 3, 4, 'Cloud Hack Lab Naming Rights & Signage', 'TechNova 2027', '2026-10-16', 'Ravi Teja', 'High', 'In Progress', NULL, 'Flex print ordered for Computer Lab 3 doorway.'),
(8, 3, 4, 'Student cloud vouchers email broadcast', 'TechNova 2027', '2026-10-12', 'Aditi Sharma', 'Medium', 'Not Started', NULL, 'Awaiting coupon code inventory from NexaWorks.'),
(9, 4, 5, 'Title Sponsor central logo on Main Auditorium Backdrop', 'TechNova 2027', '2026-10-12', 'System Administrator', 'High', 'In Progress', NULL, 'Vector artwork approved; backdrop printing underway.'),
(10, 4, 5, 'Chief Guest felicitation and 15-min inaugural address', 'TechNova 2027', '2026-10-20', 'System Administrator', 'High', 'In Progress', NULL, 'Dr. Sneha Iyer confirmed for 9:30 AM inaugural ceremony.'),
(11, 4, 5, 'VIP Hospitality Lounge branding', 'TechNova 2027', '2026-10-19', 'Ravi Teja', 'Medium', 'Not Started', NULL, 'Lounge signage setup scheduled for eve of event.'),
(12, 5, 9, 'Logo printed on Hackathon Kit Bags', 'TechNova 2027', '2026-10-05', 'Ravi Teja', 'Medium', 'Completed', '2026-10-03', 'Kit bags received from manufacturer with logo.'),
(13, 5, 9, 'Digital certificate co-branding placement', 'TechNova 2027', '2026-10-22', 'Aditi Sharma', 'Low', 'Not Started', NULL, 'Certificate template draft approved.'),
(14, 6, 11, 'Women in Tech CSR Award Felicitation on stage', 'TechNova 2027', '2026-10-21', 'System Administrator', 'High', 'In Progress', NULL, 'Trophy engraved with FinLeaf logo.'),
(15, 6, 11, 'Student scholarship cheque handover ceremony', 'TechNova 2027', '2026-10-21', 'System Administrator', 'High', 'In Progress', NULL, 'Mock presentation cheque ordered.'),
(16, 7, 14, 'Livestream video lower-third branding logo', 'TechNova 2027', '2026-10-18', 'Aditi Sharma', 'Medium', 'In Progress', NULL, 'OBS streaming graphic assets prepared.'),
(17, 7, 14, '50 complimentary student delegate pass codes', 'TechNova 2027', '2026-10-03', 'Ravi Teja', 'High', 'Completed', '2026-10-03', 'Voucher codes activated and verified.'),
(18, 8, 18, 'Dedicated Recruitment Desk in Campus Placement Cell', 'TechNova 2027', '2026-10-19', 'Ravi Teja', 'High', 'In Progress', NULL, 'Interview room #2 assigned to BrightPath.'),
(19, 8, 18, 'Logo on campus recruitment catalog', 'TechNova 2027', '2026-10-06', 'Aditi Sharma', 'Medium', 'Completed', '2026-10-04', 'Brochures printed and delivered.'),
(20, 9, 19, 'Hardware Hackathon Display Space (2 tables)', 'TechNova 2027', '2026-10-17', 'Ravi Teja', 'High', 'In Progress', NULL, 'Power strips and safety test benches prepared.'),
(21, 9, 19, 'Jury chair appointment for maker competition', 'TechNova 2027', '2026-10-15', 'Aditi Sharma', 'Medium', 'Delayed', NULL, 'Awaiting confirmation of nominated jury member name.'),
(22, 10, 21, 'Developer Relations API challenge portal integration', 'TechNova 2027', '2026-10-04', 'Aditi Sharma', 'High', 'Completed', '2026-10-03', 'GitHub repository template published.'),
(23, 10, 21, 'Promo video playback during intermission', 'TechNova 2027', '2026-10-14', 'Ravi Teja', 'Low', 'Not Started', NULL, 'Video MP4 received; awaiting AV check.'),
(24, 1, 1, 'Standee banner at main registration gate', 'TechNova 2027', '2026-10-16', 'Ravi Teja', 'Medium', 'Completed', '2026-10-05', 'Standee printed and safely stored in admin office.'),
(25, 4, 5, 'Full-page advertisement in Souvenir magazine', 'TechNova 2027', '2026-10-07', 'Aditi Sharma', 'Medium', 'Completed', '2026-10-05', 'Hi-res CMYK page layout submitted to publisher.'),
(26, 2, 3, 'Social media speaker highlight reel', 'TechNova 2027', '2026-10-12', 'Aditi Sharma', 'Low', 'In Progress', NULL, 'Reels clip in video editing queue.'),
(27, 3, 4, 'Cloud workshop attendee participation certificates', 'TechNova 2027', '2026-10-23', 'Ravi Teja', 'Low', 'Not Started', NULL, 'Template prepared in certificate generation module.'),
(28, 11, 2, 'Stall demarcation for Vertex Systems (pending agreement)', 'TechNova 2027', '2026-10-18', 'Ravi Teja', 'Medium', 'Delayed', NULL, 'Awaiting formal contract signing before stall release.');

-- --------------------------------------------------------------------
-- 9. activities
-- --------------------------------------------------------------------
INSERT INTO activities (id, company_id, activity_type, description, activity_date, created_by) VALUES
(1, 1, 'Company Added', 'NovaGrid Technologies registered as prospective sponsor.', '2026-09-08', 'Aditi Sharma'),
(2, 1, 'Outreach', 'Initial sponsorship prospectus sent via email to Anita Rao.', '2026-09-10', 'Aditi Sharma'),
(3, 1, 'Proposal', 'Gold Sponsor proposal P-101 (₹50,000) generated and shared.', '2026-09-12', 'Aditi Sharma'),
(4, 1, 'Status Change', 'Proposal accepted by Anita Rao. Company status updated to Confirmed.', '2026-09-15', 'Aditi Sharma'),
(5, 1, 'Payment', 'Received full payment of ₹50,000 via NEFT.', '2026-09-22', 'System Administrator'),
(6, 1, 'Deliverable', 'Website logo placement completed.', '2026-09-24', 'Aditi Sharma'),
(7, 2, 'Company Added', 'Vertex Systems added through referral.', '2026-09-09', 'Ravi Teja'),
(8, 2, 'Outreach', 'Introductory email sent to Marketing Manager Karthik S.', '2026-09-12', 'Ravi Teja'),
(9, 2, 'Proposal', 'Silver Sponsor proposal P-102 (₹35,000) sent.', '2026-09-20', 'Ravi Teja'),
(10, 2, 'Negotiation', 'Negotiation call held regarding booth electrical requirements.', '2026-09-22', 'Ravi Teja'),
(11, 3, 'Company Added', 'BlueOrbit Labs added to Data & AI sponsorship roster.', '2026-09-10', 'Aditi Sharma'),
(12, 3, 'Proposal', 'Silver proposal P-103 (₹40,000) approved and signed.', '2026-09-18', 'Aditi Sharma'),
(13, 3, 'Payment', 'Received 50% advance payment (₹20,000).', '2026-09-26', 'System Administrator'),
(14, 4, 'Company Added', 'NexaWorks enrolled into Cloud partner funnel.', '2026-09-09', 'Ravi Teja'),
(15, 4, 'Proposal', 'Proposal P-104 (₹30,000) accepted.', '2026-09-20', 'Ravi Teja'),
(16, 4, 'Payment', 'Full sponsorship fee of ₹30,000 cleared.', '2026-09-28', 'System Administrator'),
(17, 5, 'Company Added', 'QuantumEdge Solutions identified for Title tier.', '2026-09-05', 'System Administrator'),
(18, 5, 'Meeting', 'Executive presentation completed with Director Sneha Iyer.', '2026-09-15', 'System Administrator'),
(19, 5, 'Proposal', 'Title proposal P-105 (₹75,000) signed.', '2026-09-20', 'System Administrator'),
(20, 5, 'Payment', 'First milestone payment of ₹40,000 received.', '2026-09-27', 'System Administrator'),
(21, 9, 'Proposal', 'Bronze proposal P-108 (₹25,000) accepted by Meera J.', '2026-09-22', 'Ravi Teja'),
(22, 9, 'Payment', 'Full amount ₹25,000 paid via UPI/RTGS.', '2026-09-28', 'System Administrator'),
(23, 10, 'Outreach', 'Proposal P-109 declined due to annual budget limit.', '2026-09-20', 'Aditi Sharma'),
(24, 11, 'Meeting', 'CSR Committee meeting successfully completed.', '2026-09-25', 'System Administrator'),
(25, 11, 'Proposal', 'Gold CSR package P-110 (₹60,000) sanctioned.', '2026-09-26', 'System Administrator'),
(26, 14, 'Proposal', 'Community partnership P-111 (₹20,000) approved.', '2026-09-22', 'Ravi Teja'),
(27, 14, 'Deliverable', '50 student codes dispatched to founder Pooja R.', '2026-10-03', 'Ravi Teja'),
(28, 16, 'Proposal', 'Proposal P-106 dispatched to Business Head Isha G.', '2026-09-23', 'Ravi Teja'),
(29, 17, 'Proposal', 'Proposal P-107 viewed by brand committee.', '2026-09-24', 'Aditi Sharma'),
(30, 18, 'Proposal', 'Internship Partner proposal P-113 accepted.', '2026-09-25', 'Ravi Teja'),
(31, 19, 'Proposal', 'Maker track Silver package P-114 accepted.', '2026-09-26', 'Aditi Sharma'),
(32, 21, 'Proposal', 'API challenge partner package P-115 signed.', '2026-09-25', 'Aditi Sharma'),
(33, 22, 'Meeting', 'HelioCloud negotiation meeting concluded.', '2026-09-26', 'System Administrator'),
(34, 1, 'Deliverable', 'Standee banner verified and ready for gate deployment.', '2026-10-05', 'Ravi Teja'),
(35, 5, 'Deliverable', 'Souvenir magazine full-page color creative submitted.', '2026-10-05', 'Aditi Sharma');
