package com.example.ealert.app

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.example.ealert.databinding.ActivityChangePasswordBinding
import com.example.ealert.app.retrofit.RetrofitClient
import kotlinx.coroutines.launch

class ChangePasswordActivity : AppCompatActivity() {

    private lateinit var binding: ActivityChangePasswordBinding
    private var verificationToken: String? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityChangePasswordBinding.inflate(layoutInflater)
        setContentView(binding.root)

        verificationToken = intent.getStringExtra("verification_token") ?: ""
        setupClickListeners()
    }

    private fun setupClickListeners() {
        binding.btnBack.setOnClickListener { finish() }

        binding.imgbtnChangePW.setOnClickListener {
            val newPassword = binding.etEnterNPW.text.toString()
            val confirmPassword = binding.etConfirmPW.text.toString()

            if (validatePasswords(newPassword, confirmPassword)) {
                resetPassword(newPassword)
            }
        }
    }

    private fun validatePasswords(newPassword: String, confirmPassword: String): Boolean {
        if (newPassword.isEmpty()) {
            binding.etEnterNPW.error = "Please enter new password"
            return false
        }

        if (confirmPassword.isEmpty()) {
            binding.etConfirmPW.error = "Please confirm password"
            return false
        }

        if (newPassword.length < 8) {
            binding.etEnterNPW.error = "Password must be at least 8 characters"
            return false
        }

        if (newPassword != confirmPassword) {
            binding.etConfirmPW.error = "Passwords don't match"
            return false
        }

        return true
    }

    private fun resetPassword(newPassword: String) {
        verificationToken?.let { token ->
            lifecycleScope.launch {
                binding.progressBar.visibility = View.VISIBLE
                binding.imgbtnChangePW.isEnabled = false

                try {
                    val response = RetrofitClient.instance.resetPassword(token, newPassword)
                    if (response.isSuccessful && response.body()?.status == "success") {
                        Toast.makeText(
                            this@ChangePasswordActivity,
                            "Password changed successfully",
                            Toast.LENGTH_SHORT
                        ).show()

                        val intent = Intent(this@ChangePasswordActivity, LoginActivity::class.java).apply {
                            flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
                        }
                        startActivity(intent)
                        finish()
                    } else {
                        Toast.makeText(
                            this@ChangePasswordActivity,
                            response.body()?.message ?: "Failed to change password",
                            Toast.LENGTH_SHORT
                        ).show()
                    }
                } catch (e: Exception) {
                    Toast.makeText(
                        this@ChangePasswordActivity,
                        "Error: ${e.message}",
                        Toast.LENGTH_SHORT
                    ).show()
                } finally {
                    binding.progressBar.visibility = View.GONE
                    binding.imgbtnChangePW.isEnabled = true
                }
            }
        } ?: run {
            Toast.makeText(
                this@ChangePasswordActivity,
                "Verification token missing",
                Toast.LENGTH_SHORT
            ).show()
        }
    }
}