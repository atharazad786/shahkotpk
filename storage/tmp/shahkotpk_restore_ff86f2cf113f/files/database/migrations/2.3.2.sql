-- ShahkotPK v2.3.2 Premium Sidebar + Admin Identity
ALTER TABLE user_profiles
  ADD COLUMN avatar_url VARCHAR(500) NULL AFTER notes;
